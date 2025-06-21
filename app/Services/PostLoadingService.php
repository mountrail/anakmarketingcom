<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostLoadingService
{
    /**
     * Get posts for the index page with featured posts
     */
    public function getPostsForIndex(string $selectedType, int $perPage = 10): array
    {
        // Get featured posts (editor's picks) filtered by the selected type for the main content
        $typedEditorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->where('type', $selectedType)
            ->with(['user', 'answers', 'images'])
            ->latest()
            ->get();

        // Get the IDs of featured posts to exclude them from regular posts
        $featuredPostIds = $typedEditorPicks->pluck('id')->toArray();

        // Get regular posts filtered by type, excluding featured posts
        $posts = Post::where('type', $selectedType)
            ->whereNotIn('id', $featuredPostIds)
            ->with(['user', 'answers', 'images'])
            ->latest()
            ->paginate($perPage);

        // Get editor's picks from both categories for the sidebar
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers', 'images'])
            ->latest()
            ->get();

        return [
            'typedEditorPicks' => $typedEditorPicks,
            'posts' => $posts,
            'editorPicks' => $editorPicks
        ];
    }

    /**
     * Load more posts for a specific user (for AJAX requests)
     */
    public function loadUserPosts(Request $request, User $user): array
    {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 2);
        $currentPostId = $request->get('current_post_id', null);
        $postType = $request->get('post_type', 'own');

        if ($postType === 'answered') {
            $postsQuery = Post::whereHas('answers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('user_id', '!=', $user->id)->withCount('answers')->with('images')->latest();
        } else {
            $postsQuery = $user->posts()->withCount('answers')->with('images')->latest();
        }

        if ($currentPostId) {
            $postsQuery->where('id', '!=', $currentPostId);
        }

        $posts = $postsQuery->skip($offset)->take($limit)->get();

        return [
            'posts' => $posts,
            'count' => $posts->count()
        ];
    }

    /**
     * Toggle the featured status of a post (Editor's Pick)
     */
    public function toggleFeatured(Post $post): array
    {
        if (!Auth::user()->hasRole(['editor', 'admin'])) {
            return [
                'success' => false,
                'message' => 'Unauthorized action.',
                'status_code' => 403
            ];
        }

        $wasFeatured = $post->is_featured;

        if ($post->featured_type === 'none') {
            $post->is_featured = true;
            $post->featured_type = 'editors_pick';
        } else {
            $post->is_featured = false;
            $post->featured_type = 'none';
        }

        $post->save();

        return [
            'success' => true,
            'is_featured' => $post->is_featured,
            'message' => $post->is_featured
                ? 'Post added to Editor\'s Picks successfully'
                : 'Post removed from Editor\'s Picks successfully'
        ];
    }
}
