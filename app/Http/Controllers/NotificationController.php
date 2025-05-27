<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     * Filtering is now handled client-side with JavaScript.
     */
    public function index(Request $request)
    {
        $baseQuery = auth()->user()->notifications();

        // Get pinned notifications separately (not paginated)
        $pinnedNotifications = (clone $baseQuery)
            ->where(function ($q) {
                $q->whereJsonContains('data->is_pinned', true)
                    ->orWhereJsonContains('data->is_pinned', 1);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get regular notifications (paginated)
        $regularNotifications = (clone $baseQuery)
            ->where(function ($q) {
                $q->whereJsonDoesntContain('data->is_pinned', true)
                    ->whereJsonDoesntContain('data->is_pinned', 1)
                    ->orWhereNull('data->is_pinned');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('pinnedNotifications', 'regularNotifications'));
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
}
