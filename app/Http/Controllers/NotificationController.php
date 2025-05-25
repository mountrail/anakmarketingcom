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
                            ->orWhereJsonContains('data->type', 'announcement')
                            ->orWhereJsonContains('data->type', 'system')
                            ->orWhereNull('data->type'); // For backward compatibility
                    });
                    break;
            }
        }

        $notifications = $query->paginate(20);

        return view('notifications.index', compact('notifications', 'category'));
    }

    /**
     * Mark a specific notification as read.
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
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'unread_count' => 0
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

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }
}
