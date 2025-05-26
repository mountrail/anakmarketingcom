<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications with category filtering.
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'Semua');

        $query = auth()->user()->notifications();

        // Apply category filtering
        if ($category !== 'Semua') {
            switch ($category) {
                case 'Pertanyaan / Diskusi Saya':
                    // Notifications about answers to user's posts
                    $query->whereJsonContains('data->type', 'post_answered');
                    break;

                case 'Pertanyaan / Diskusi yang Diikuti':
                    // Notifications about new posts from followed users
                    $query->whereJsonContains('data->type', 'followed_user_posted');
                    break;

                case 'Lainnya':
                    // Follow notifications, announcements, and other types
                    $query->where(function ($q) {
                        $q->whereJsonContains('data->type', 'user_followed')
                            ->orWhereJsonContains('data->type', 'badge_earned')
                            ->orWhereJsonContains('data->type', 'announcement')
                            ->orWhereJsonContains('data->type', 'system')
                            ->orWhereNull('data->type'); // For backward compatibility
                    });
                    break;
            }
        }

        // Order notifications: pinned first, then by created_at desc
        $notifications = $query->orderByRaw("
            CASE
                WHEN JSON_EXTRACT(data, '$.is_pinned') = true THEN 0
                ELSE 1
            END,
            created_at DESC
        ")->paginate(20);

        return view('notifications.index', compact('notifications', 'category'));
    }

    /**
     * Mark notification as read and redirect to action URL.
     * This replaces the AJAX approach for better reliability.
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
     * Mark all notifications as read.
     * Updated to use regular form submission instead of AJAX.
     */
    public function markAllAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi telah ditandai sebagai sudah dibaca.');
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
     * Delete a notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        // Check if notification is pinned (pinned notifications cannot be deleted)
        $isPinned = isset($notification->data['is_pinned']) && $notification->data['is_pinned'];

        if ($isPinned) {
            return response()->json(['error' => 'Pinned notifications cannot be deleted'], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }
}
