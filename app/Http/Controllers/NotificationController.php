<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     * Auto-mark all unread notifications as read when user visits the page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get unread notification IDs before marking them as read
        $unreadNotificationIds = $user->unreadNotifications->pluck('id')->toArray();

        // Auto-mark all unread notifications as read when user visits notification page
        $user->unreadNotifications->markAsRead();

        // Get custom notifications and convert them to notification-like objects
        $customNotifications = \App\Models\CustomNotification::where('is_active', true)
            ->with('creator')
            ->get()
            ->map(function ($custom) {
                return (object) [
                    'id' => 'custom_' . $custom->id,
                    'type' => 'App\\Notifications\\CustomNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => auth()->id(),
                    'data' => [
                        'type' => 'custom_notification',
                        'category' => 'system',
                        'title' => $custom->title,
                        'message' => $custom->message,
                        'action_url' => $custom->getActionUrl(),
                        'is_pinned' => $custom->is_pinned,
                        'use_creator_avatar' => $custom->use_creator_avatar,
                        'creator_avatar' => $custom->use_creator_avatar && $custom->creator ? $custom->creator->getProfileImageUrl() : null,
                    ],
                    'read_at' => now(), // Custom notifications are always considered "read"
                    'created_at' => $custom->created_at,
                    'updated_at' => $custom->updated_at,
                ];
            });

        $baseQuery = $user->notifications();

        // Get all user notifications
        $userNotifications = $baseQuery->orderBy('created_at', 'desc')->get();
        // Preload users for avatar URLs to prevent N+1 queries
        $userIds = collect();
        foreach ($userNotifications as $notification) {
            if (isset($notification->data['follower_id'])) {
                $userIds->push($notification->data['follower_id']);
            } elseif (isset($notification->data['answerer_id'])) {
                $userIds->push($notification->data['answerer_id']);
            } elseif (isset($notification->data['poster_id'])) {
                $userIds->push($notification->data['poster_id']);
            }
        }

        // Cache users for the view
        $notificationUsers = \App\Models\User::whereIn('id', $userIds->unique())->get()->keyBy('id');

        // Merge custom notifications with user notifications
        $allNotifications = $customNotifications->merge($userNotifications);

        // Sort by pinned status first, then by creation date
        $allNotifications = $allNotifications->sortByDesc(function ($notification) {
            $isPinned = isset($notification->data['is_pinned']) &&
                ($notification->data['is_pinned'] === true || $notification->data['is_pinned'] === 1);
            return $isPinned ? 1 : 0;
        })->sortByDesc('created_at');

        // Separate pinned and regular notifications
        $pinnedNotifications = $allNotifications->filter(function ($notification) {
            return isset($notification->data['is_pinned']) &&
                ($notification->data['is_pinned'] === true || $notification->data['is_pinned'] === 1);
        });

        $regularNotifications = $allNotifications->filter(function ($notification) {
            return !isset($notification->data['is_pinned']) ||
                ($notification->data['is_pinned'] !== true && $notification->data['is_pinned'] !== 1);
        });

        // Paginate regular notifications properly
        $regularNotifications = $regularNotifications->forPage(1, 20);

        return view('notifications.index', compact(
            'pinnedNotifications',
            'regularNotifications',
            'unreadNotificationIds',
            'notificationUsers' // Add this
        ));
    }

    /**
     * Mark notification as read and redirect to action URL.
     */
    public function read(Request $request, string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
        }

        // Get redirect URL from request parameter or notification data
        $redirectUrl = $request->get('redirect');

        if (!$redirectUrl && $notification) {
            $actionUrl = $notification->data['action_url'] ?? null;

            // Convert relative URL to absolute URL using url() helper
            if ($actionUrl) {
                $redirectUrl = $this->makeAbsoluteUrl($actionUrl);
            }
        }

        // If no redirect URL, go back to notifications page
        if (!$redirectUrl) {
            return redirect()->route('notifications.index')
                ->with('success', 'Notifikasi telah ditandai sebagai sudah dibaca.');
        }

        return redirect($redirectUrl);
    }

    /**
     * Convert relative URL to absolute URL
     */
    private function makeAbsoluteUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        // If it's already an absolute URL, return as is
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // If it starts with '/', it's a relative URL from root
        if (strpos($url, '/') === 0) {
            return url($url);
        }

        // Otherwise, treat it as a path relative to current domain
        return url('/' . ltrim($url, '/'));
    }

    /**
     * Delete all non-system notifications.
     */
    public function deleteAll(Request $request)
    {
        $user = auth()->user();

        // Delete all notifications except system notifications
        $user->notifications()
            ->where(function ($query) {
                $query->whereJsonDoesntContain('data->category', 'system')
                    ->orWhereNull('data->category');
            })
            ->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi non-sistem telah dihapus.');
    }

    /**
     * Mark a specific notification as read (AJAX endpoint - kept for backward compatibility).
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Get unread notifications count (for AJAX requests).
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = auth()->user()->unreadNotifications->count();

        return response()->json([
            'unread_count' => $count
        ]);
    }

    /**
     * Delete specific notification.
     */
    public function delete(string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return redirect()->route('notifications.index')
                ->with('error', 'Notifikasi tidak ditemukan.');
        }

        // Check if it's a system notification
        $category = $notification->data['category'] ?? null;
        if ($category === 'system') {
            return redirect()->route('notifications.index')
                ->with('error', 'Notifikasi sistem tidak dapat dihapus.');
        }

        $notification->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'Notifikasi telah dihapus.');
    }

    public function showCustomNotification(\App\Models\CustomNotification $customNotification)
    {
        if (!$customNotification->is_active) {
            abort(404);
        }

        $actionUrl = $customNotification->getActionUrl();

        if ($actionUrl) {
            return redirect($actionUrl);
        }

        return redirect()->route('notifications.index');
    }
}
