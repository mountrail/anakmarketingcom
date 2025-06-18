<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PostViewService
{
    /**
     * Get editor picks for sidebar
     */
    public function getEditorPicks(int $limit = null): Collection
    {
        return Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers', 'images'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Share editor picks with all views
     */
    public function shareEditorPicks(int $limit = null): void
    {
        $editorPicks = $this->getEditorPicks($limit);
        view()->share('editorPicks', $editorPicks);
    }

    /**
     * Load post with relationships for display
     */
    public function loadPostForDisplay(Post $post): Post
    {
        return $post->load([
            'user',
            'answers' => function ($query) {
                $query->latest();
            },
            'answers.user',
            'images'
        ]);
    }

    /**
     * Check if user can see view count
     */
    public function canSeeViewCount(Post $post, $user = null): bool
    {
        if (!$user) {
            return false;
        }

        // Post owner can see
        if ($post->user_id === $user->id) {
            return true;
        }

        // Admin/Editor can see using Spatie roles
        if ($user->hasRole(['admin', 'editor'])) {
            return true;
        }

        return false;
    }

    /**
     * Record initial view (for tracking purposes)
     */
    public function recordInitialView(Post $post): void
    {
        $sessionId = session()->getId();
        $viewKey = "post_view_{$post->id}_{$sessionId}";

        // Store initial view timestamp (expires in 2 hours)
        $timestamp = Carbon::now();
        Cache::put($viewKey, $timestamp->timestamp, 7200);

        Log::info("POST_VIEW: Initial view recorded", [
            'post_id' => $post->id,
            'viewKey' => $viewKey,
            'timestamp' => $timestamp->toDateTimeString()
        ]);
    }

    /**
     * Increment post view count after specified seconds
     */
    public function incrementViewCount(Post $post, int $minimumSeconds = 45): array
    {
        $sessionId = session()->getId();
        $viewKey = "post_view_{$post->id}_{$sessionId}";

        // Check if initial view was recorded
        $initialViewTimestamp = Cache::get($viewKey);
        if (!$initialViewTimestamp) {
            Log::info("POST_VIEW: No initial view found, recording now", [
                'post_id' => $post->id,
                'viewKey' => $viewKey
            ]);

            // Record initial view now and require minimum time
            $this->recordInitialView($post);
            return [
                'success' => false,
                'message' => 'Initial view just recorded, not enough time elapsed',
                'view_count' => $post->view_count
            ];
        }

        // Calculate seconds elapsed
        $currentTimestamp = Carbon::now()->timestamp;
        $secondsElapsed = $currentTimestamp - $initialViewTimestamp;

        if ($secondsElapsed >= $minimumSeconds) {
            try {
                // Increment the view count
                $post->increment('view_count');
                $newCount = $post->fresh()->view_count;

                // Clear the initial view so they can get another count on next visit
                Cache::forget($viewKey);

                Log::info("POST_VIEW: Successfully incremented", [
                    'post_id' => $post->id,
                    'new_count' => $newCount,
                    'seconds_elapsed' => $secondsElapsed
                ]);

                return [
                    'success' => true,
                    'message' => 'View count incremented',
                    'view_count' => $newCount
                ];
            } catch (\Exception $e) {
                Log::error("POST_VIEW: Failed to increment", [
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);

                return [
                    'success' => false,
                    'message' => 'Database error',
                    'view_count' => $post->view_count
                ];
            }
        }

        Log::info("POST_VIEW: Not enough time elapsed", [
            'post_id' => $post->id,
            'seconds_elapsed' => $secondsElapsed,
            'required' => $minimumSeconds
        ]);

        return [
            'success' => false,
            'message' => "Only {$secondsElapsed} seconds elapsed, need {$minimumSeconds}",
            'view_count' => $post->view_count
        ];
    }

    /**
     * Render post items for AJAX responses
     */
    public function renderPostItems(Collection $posts, array $options = []): string
    {
        $html = '';
        $defaultOptions = [
            'showMeta' => false,
            'showVoteScore' => false,
            'showCommentCount' => true,
            'showShare' => true,
            'showThreeDots' => true,
            'customClasses' => 'text-xs',
            'containerClasses' => 'border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0'
        ];

        $options = array_merge($defaultOptions, $options);

        foreach ($posts as $post) {
            $html .= view('components.post-item', array_merge($options, [
                'post' => $post
            ]))->render();
        }

        return $html;
    }
}
