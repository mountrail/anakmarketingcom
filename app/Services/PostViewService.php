<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class PostViewService
{
    /**
     * Get editor picks for sidebar
     */
    public function getEditorPicks(int $limit = 5): Collection
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
    public function shareEditorPicks(int $limit = 5): void
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
     * Increment post view count
     */
    public function incrementViewCount(Post $post): void
    {
        $post->increment('view_count');
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
