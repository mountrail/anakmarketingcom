<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostSlugRedirect;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            ->with(['user', 'answers', 'media'])
            ->latest()
            ->get();

        // Get the IDs of featured posts to exclude them from regular posts
        $featuredPostIds = $typedEditorPicks->pluck('id')->toArray();

        // Get regular posts filtered by type, excluding featured posts
        $posts = Post::where('type', $selectedType)
            ->whereNotIn('id', $featuredPostIds)
            ->with(['user', 'answers', 'media'])
            ->latest()
            ->paginate(10);

        // Get editor's picks from both categories for the sidebar
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers', 'media'])
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
            ->with(['user', 'answers', 'media'])
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
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'type' => 'required|in:question,discussion',
                'images.*' => 'nullable|file|image|max:2048', // 2MB max per image
            ]);

            $purifiedContent = Purifier::clean($validated['content']);

            $post = Post::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'content' => $purifiedContent,
                'type' => $validated['type'],
            ]);

            // Handle uploaded images using Spatie Media Library
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $post->addMediaFromRequest('images')
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('images');
                }
            }

            // Handle temporary images from editor (if using the temp upload system)
            if ($request->has('uploaded_images')) {
                $tempImages = json_decode($request->uploaded_images, true);

                if (!empty($tempImages) && is_array($tempImages)) {
                    foreach ($tempImages as $tempImage) {
                        if (isset($tempImage['temp_path']) && Storage::disk('public')->exists($tempImage['temp_path'])) {
                            // Move from temp storage to media library
                            $post->addMediaFromDisk($tempImage['temp_path'], 'public')
                                ->usingName($tempImage['name'] ?? 'Uploaded image')
                                ->toMediaCollection('images');

                            // Clean up temp file
                            Storage::disk('public')->delete($tempImage['temp_path']);
                        }
                    }
                }
            }

            // Check for badge
            BadgeService::checkBreakTheIce(Auth::user());

            // Send notification to followers
            if (auth()->user()->followers()->exists()) {
                $followers = auth()->user()->followers()->get();
                foreach ($followers as $follower) {
                    $follower->notify(new \App\Notifications\FollowedUserPostedNotification($post, auth()->user()));
                }
            }

            $successMessage = $validated['type'] === 'question' ? 'Pertanyaan berhasil dibuat!' : 'Diskusi berhasil dibuat!';

            return redirect()->route('posts.show', $post->slug)
                ->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Terdapat kesalahan dalam form. Silakan periksa kembali.');

        } catch (\Exception $e) {
            Log::error('Error creating post: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token', 'uploaded_images', 'images']),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat post. Silakan coba lagi.');
        }
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

        // Load post relationships including media
        $post->load([
            'user',
            'answers' => function ($query) {
                $query->latest();
            },
            'answers.user',
            'media'
        ]);

        // Share editorPicks for the sidebar
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers', 'media'])
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

        $post->load('media');

        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers', 'media'])
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

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'type' => 'required|in:question,discussion',
                'images.*' => 'nullable|file|image|max:2048', // New images
                'keep_images' => 'nullable|array', // IDs of existing images to keep
                'keep_images.*' => 'integer|exists:media,id',
            ]);

            $purifiedContent = Purifier::clean($validated['content']);

            // Store original slug for redirect comparison
            $originalSlug = $post->slug;

            $post->update([
                'title' => $validated['title'],
                'content' => $purifiedContent,
                'type' => $validated['type'],
            ]);

            // Handle existing images - remove those not in keep_images array
            $keepImageIds = $request->get('keep_images', []);
            $existingMedia = $post->getMedia('images');

            foreach ($existingMedia as $media) {
                if (!in_array($media->id, $keepImageIds)) {
                    $media->delete(); // Spatie will handle file deletion automatically
                }
            }

            // Add new images if any
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $post->addMediaFromRequest('images')
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('images');
                }
            }

            $successMessage = $validated['type'] === 'question' ? 'Pertanyaan berhasil diperbarui!' : 'Diskusi berhasil diperbarui!';

            // Use fresh() to get updated model data including potentially new slug
            $updatedPost = $post->fresh();

            return redirect()->route('posts.show', $updatedPost->slug)
                ->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Terdapat kesalahan dalam form. Silakan periksa kembali.');

        } catch (\Exception $e) {
            Log::error('Error updating post: ' . $e->getMessage(), [
                'post_id' => $post->id,
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token', 'images']),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui post. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Delete associated media (Spatie will handle file cleanup automatically)
            $post->clearMediaCollection('images');

            // Delete associated slug redirects
            $post->slugRedirects()->delete();

            // Delete the post
            $post->delete();

            return redirect()->route('home')
                ->with('success', 'Post berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error deleting post: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus post. Silakan coba lagi.');
        }
    }

    /**
     * Toggle the featured status of a post (Editor's Pick) - Enhanced for AJAX
     */
    public function toggleFeatured(Post $post)
    {
        if (!Auth::user()->hasRole(['editor', 'admin'])) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
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

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_featured' => $post->is_featured,
                'message' => $post->is_featured
                    ? 'Post added to Editor\'s Picks successfully'
                    : 'Post removed from Editor\'s Picks successfully'
            ]);
        }

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
            })->where('user_id', '!=', $user->id)->withCount('answers')->with('media')->latest();
        } else {
            $postsQuery = $user->posts()->withCount('answers')->with('media')->latest();
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
                route('posts.show', $post->slug),
                $request->boolean('is_pinned'),
                $post
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Announcement sent successfully!'
        ]);
    }

    /**
     * Delete a specific image from a post
     */
    public function deleteImage(Post $post, $mediaId)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $media = $post->getMedia('images')->where('id', $mediaId)->first();

            if ($media) {
                $media->delete();

                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Image deleted successfully'
                    ]);
                }

                return redirect()->back()->with('success', 'Image deleted successfully');
            }

            return response()->json(['success' => false, 'message' => 'Image not found'], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting image: ' . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting image'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error deleting image');
        }
    }
}
