<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Notifications\AnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AdminNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasRole(['admin', 'editor'])) {
                abort(403, 'Unauthorized access.');
            }
            return $next($request);
        });
    }

    /**
     * Send announcement notification to all users
     */
    public function sendAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'action_url' => 'nullable|url',
            'is_pinned' => 'boolean',
            'post_id' => 'nullable|exists:posts,id'
        ]);

        $post = $request->post_id ? Post::find($request->post_id) : null;

        // Get all users to send notification to
        $users = User::all();

        // Send notification to all users
        Notification::send($users, new AnnouncementNotification(
            $request->title,
            $request->message,
            $request->action_url,
            $request->boolean('is_pinned'),
            $post
        ));

        return response()->json([
            'success' => true,
            'message' => 'Announcement sent to all users successfully!'
        ]);
    }

    /**
     * Send personalized announcement to specific users
     */
    public function sendPersonalizedAnnouncement(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'action_url' => 'nullable|url',
            'is_pinned' => 'boolean',
            'post_id' => 'nullable|exists:posts,id'
        ]);

        $post = $request->post_id ? Post::find($request->post_id) : null;

        // Get specific users
        $users = User::whereIn('id', $request->user_ids)->get();

        // Send notification to selected users
        Notification::send($users, new AnnouncementNotification(
            $request->title,
            $request->message,
            $request->action_url,
            $request->boolean('is_pinned'),
            $post
        ));

        return response()->json([
            'success' => true,
            'message' => 'Announcement sent to selected users successfully!'
        ]);
    }
}
