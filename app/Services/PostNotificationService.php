<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Notifications\FollowedUserPostedNotification;
use App\Notifications\AnnouncementNotification;
use Illuminate\Support\Facades\Notification;

class PostNotificationService
{
    /**
     * Send notifications to followers when user creates a new post
     */
    public function notifyFollowersOfNewPost(Post $post, User $author): void
    {
        if (!$author->followers()->exists()) {
            return;
        }

        $followers = $author->followers()->get();
        foreach ($followers as $follower) {
            $follower->notify(new FollowedUserPostedNotification($post, $author));
        }
    }

    /**
     * Send admin announcement about a specific post
     */
    public function sendPostAnnouncement(Post $post, string $message, bool $isPinned = false, User $sender): void
    {
        $users = User::where('id', '!=', $sender->id)->get();

        Notification::send(
            $users,
            new AnnouncementNotification(
                'Recommended Discussion',
                $message,
                route('posts.show', $post->slug),
                $isPinned,
                $post
            )
        );
    }
}
