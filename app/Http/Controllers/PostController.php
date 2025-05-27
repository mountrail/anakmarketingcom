<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostSlugRedirect;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
{
    /**
     * Display a listing of posts based on type filter.
     */
    public function index(Request $request)
    {
        $selectedType = $request->query('type', 'question');

        // Get featured posts (editor's picks) filtered by the selected type for the main content
        $typedEditorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->where('type', $selectedType)
            ->with(['user', 'answers'])
            ->latest()
            ->get();

        // Get the IDs of featured posts to exclude them from regular posts
        $featuredPostIds = $typedEditorPicks->pluck('id')->toArray();

        // Get regular posts filtered by type, excluding featured posts
        $posts = Post::where('type', $selectedType)
            ->whereNotIn('id', $featuredPostIds)
            ->with(['user', 'answers'])
            ->latest()
            ->paginate(10);

        // Get editor's picks from both categories for the sidebar
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers'])
            ->latest()
            ->take(5)
            ->get();

        view()->share('editorPicks', $editorPicks);

        return view('home.index', compact('selectedType', 'posts', 'typedEditorPicks'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers'])
            ->latest()
            ->take(5)
            ->get();

        view()->share('editorPicks', $editorPicks);

        return view('posts.create');
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:question,discussion',
        ]);

        $purifiedContent = Purifier::clean($validated['content']);

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $purifiedContent,
            'type' => $validated['type'],
        ]);

        // The slug is automatically updated in the model's boot method

        // Check for badge
        BadgeService::checkBreakTheIce(Auth::user());

        // Send notification to followers
        if (auth()->user()->followers()->exists()) {
            $followers = auth()->user()->followers()->get();
            foreach ($followers as $follower) {
                $follower->notify(new \App\Notifications\FollowedUserPostedNotification($post, auth()->user()));
            }
        }

        // Handle uploaded images
        if ($request->has('uploaded_images')) {
            $images = json_decode($request->uploaded_images, true);

            if (!empty($images) && is_array($images)) {
                foreach ($images as $image) {
                    $post->images()->create([
                        'url' => $image['url'],
                        'name' => $image['name'] ?? 'Uploaded image',
                    ]);
                }
            }
        }

        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post with comprehensive redirect handling.
     */
    public function show($slug)
    {
        // First, try to find the post by slug
        $post = Post::where('slug', $slug)->first();

        // If not found, check for redirects and handle various cases
        if (!$post) {
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

            // If still not found, 404
            abort(404);
        }

        // Increment view count
        $post->increment('view_count');

        // Load post relationships
        $post->load([
            'user',
            'answers' => function ($query) {
                $query->latest();
            },
            'answers.user',
            'images'
        ]);

        // Share editorPicks for the sidebar
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers'])
            ->latest()
            ->take(5)
            ->get();

        view()->share('editorPicks', $editorPicks);

        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $post->load('images');

        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers'])
            ->latest()
            ->take(5)
            ->get();

        view()->share('editorPicks', $editorPicks);

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:question,discussion',
        ]);

        $purifiedContent = Purifier::clean($validated['content']);

        // Store old slug before update
        $oldSlug = $post->slug;

        $post->update([
            'title' => $validated['title'],
            'content' => $purifiedContent,
            'type' => $validated['type'],
        ]);

        // The slug update and redirect creation is handled in the model

        // Handle uploaded images
        if ($request->has('uploaded_images')) {
            $newImages = json_decode($request->uploaded_images, true);
            $existingImageIds = $post->images->pluck('id')->toArray();
            $newImageIds = [];

            if (!empty($newImages) && is_array($newImages)) {
                foreach ($newImages as $image) {
                    if (isset($image['id']) && strpos($image['id'], 'img-') === 0) {
                        $post->images()->create([
                            'url' => $image['url'],
                            'name' => $image['name'] ?? 'Uploaded image',
                        ]);
                    } else if (isset($image['id'])) {
                        $newImageIds[] = $image['id'];
                    }
                }
            }

            $imagesToDelete = array_diff($existingImageIds, $newImageIds);
            if (!empty($imagesToDelete)) {
                $post->images()->whereIn('id', $imagesToDelete)->delete();
            }
        } else {
            $post->images()->delete();
        }

        // Always use the current slug for redirect (it might have changed)
        return redirect()->route('posts.show', $post->fresh()->slug)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Delete associated images
        $post->images()->delete();

        // Delete associated slug redirects
        $post->slugRedirects()->delete();

        // Delete the post
        $post->delete();

        return redirect()->route('home')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Toggle the featured status of a post (Editor's Pick)
     */
    public function toggleFeatured(Post $post)
    {
        if (!Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        if ($post->featured_type === 'none') {
            $post->is_featured = true;
            $post->featured_type = 'editors_pick';
        } else {
            $post->is_featured = false;
            $post->featured_type = 'none';
        }

        $post->save();

        return redirect()->back()
            ->with('success', 'Editor\'s pick status updated successfully.');
    }

    /**
     * Load more posts for a specific user (AJAX)
     */
    public function loadUserPosts(Request $request, User $user)
    {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 2);
        $currentPostId = $request->get('current_post_id', null);
        $postType = $request->get('post_type', 'own');

        if ($postType === 'answered') {
            $postsQuery = Post::whereHas('answers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('user_id', '!=', $user->id)->withCount('answers')->latest();
        } else {
            $postsQuery = $user->posts()->withCount('answers')->latest();
        }

        if ($currentPostId) {
            $postsQuery->where('id', '!=', $currentPostId);
        }

        $posts = $postsQuery->skip($offset)->take($limit)->get();

        if ($request->wantsJson()) {
            $html = '';
            foreach ($posts as $post) {
                $html .= view('components.post-item', [
                    'post' => $post,
                    'showMeta' => false,
                    'showVoteScore' => false,
                    'showCommentCount' => true,
                    'showShare' => true,
                    'showThreeDots' => true,
                    'customClasses' => 'text-xs',
                    'containerClasses' => 'border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0'
                ])->render();
            }

            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $posts->count()
            ]);
        }

        return response()->json(['success' => false], 400);
    }

    /**
     * Send admin announcement about a specific post
     */
    public function sendPostAnnouncement(Request $request, Post $post)
    {
        if (!Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'message' => 'required|string|max:1000',
            'is_pinned' => 'boolean'
        ]);

        $users = \App\Models\User::where('id', '!=', Auth::id())->get();

        \Illuminate\Support\Facades\Notification::send(
            $users,
            new \App\Notifications\AnnouncementNotification(
                'Recommended Discussion',
                $request->message,
                route('posts.show', $post->slug), // Use slug instead of ID
                $request->boolean('is_pinned'),
                $post
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Announcement sent successfully!'
        ]);
    }
}
