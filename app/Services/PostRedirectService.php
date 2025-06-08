<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostSlugRedirect;
use Illuminate\Http\RedirectResponse;

class PostRedirectService
{
    /**
     * Handle post slug redirects and backward compatibility
     */
    public function handlePostRedirect(string $slug): ?RedirectResponse
    {
        // First, try to find the post by slug (new format: user_id/title-id)
        $post = Post::where('slug', $slug)->first();

        if ($post) {
            return null; // No redirect needed
        }

        // Check if it's an old slug that needs redirecting
        $redirect = PostSlugRedirect::where('old_slug', $slug)->first();

        if ($redirect && $redirect->post) {
            // Permanent redirect to new slug
            return redirect()->route('posts.show', $redirect->post->slug, 301);
        }

        // If it's numeric, try to find by ID (backward compatibility)
        if (is_numeric($slug)) {
            $post = Post::find($slug);
            if ($post) {
                // Redirect to proper slug URL
                return redirect()->route('posts.show', $post->slug, 301);
            }
        }

        // Check if it's old format slug (title-id without user_id)
        if (preg_match('/^(.+)-(\d+)$/', $slug, $matches)) {
            $postId = $matches[2];
            $post = Post::find($postId);

            if ($post) {
                // Redirect to new format
                return redirect()->route('posts.show', $post->slug, 301);
            }
        }

        // If still not found, return null (caller should handle 404)
        return null;
    }

    /**
     * Get post by slug or return null
     */
    public function getPostBySlug(string $slug): ?Post
    {
        return Post::where('slug', $slug)->first();
    }
}
