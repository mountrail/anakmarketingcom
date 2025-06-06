<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class PostAuthorizationService
{
    /**
     * Check if user can edit the post
     */
    public function canEdit(Post $post, User $user): bool
    {
        return $post->user_id === $user->id || $user->hasRole(['editor', 'admin']);
    }

    /**
     * Check if user can delete the post
     */
    public function canDelete(Post $post, User $user): bool
    {
        return $post->user_id === $user->id || $user->hasRole(['editor', 'admin']);
    }

    /**
     * Check if user can manage featured status
     */
    public function canManageFeatured(User $user): bool
    {
        return $user->hasRole(['editor', 'admin']);
    }

    /**
     * Check if user can send announcements
     */
    public function canSendAnnouncements(User $user): bool
    {
        return $user->hasRole(['editor', 'admin']);
    }

    /**
     * Authorize user action or abort
     */
    public function authorizeOrAbort(bool $authorized, int $statusCode = 403, string $message = 'Unauthorized action.'): void
    {
        if (!$authorized) {
            abort($statusCode, $message);
        }
    }
}
