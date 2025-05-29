<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostSlugRedirect;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\PostImageService;
use App\Services\PostLoadingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    protected PostImageService $imageService;
    protected PostLoadingService $loadingService;

    public function __construct(PostImageService $imageService, PostLoadingService $loadingService)
    {
        $this->imageService = $imageService;
        $this->loadingService = $loadingService;
    }

    /**
     * Display a listing of posts based on type filter.
     */
    public function index(Request $request)
    {
        $selectedType = $request->query('type', 'question');

        $data = $this->loadingService->getPostsForIndex($selectedType, 10);

        view()->share('editorPicks', $data['editorPicks']);

        return view('home.index', [
            'selectedType' => $selectedType,
            'posts' => $data['posts'],
            'typedEditorPicks' => $data['typedEditorPicks']
        ]);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers', 'images'])
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
                'uploaded_images' => 'nullable|string', // JSON string of uploaded images
            ]);

            $purifiedContent = Purifier::clean($validated['content']);

            $post = Post::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'content' => $purifiedContent,
                'type' => $validated['type'],
            ]);

            // Handle image uploads using the service
            $this->imageService->handlePostImages($request, $post);

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

        // Load post relationships including images
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
            ->with(['user', 'answers', 'images'])
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
            ->with(['user', 'answers', 'images'])
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
                'images.*' => 'nullable|file|image|max:2048', // New traditional file uploads
                'uploaded_images' => 'nullable|string', // JSON string of uploaded images
                'keep_images' => 'nullable|array', // IDs of existing images to keep (legacy support)
                'keep_images.*' => 'integer|exists:post_images,id',
            ]);

            $purifiedContent = Purifier::clean($validated['content']);

            $post->update([
                'title' => $validated['title'],
                'content' => $purifiedContent,
                'type' => $validated['type'],
            ]);

            // Handle image updates using the service
            $this->imageService->handleImageUpdates($request, $post);

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
                'request_data' => $request->except(['_token', 'images', 'uploaded_images']),
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
            // Delete associated images using the service
            $this->imageService->deleteAllPostImages($post);

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
        $result = $this->loadingService->toggleFeatured($post);

        if (!$result['success'] && isset($result['status_code'])) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], $result['status_code']);
            }
            abort($result['status_code'], $result['message']);
        }

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->back()
            ->with('success', 'Editor\'s pick status updated successfully.');
    }

    /**
     * Load more posts for a specific user (AJAX)
     */
    public function loadUserPosts(Request $request, User $user)
    {
        $result = $this->loadingService->loadUserPosts($request, $user);

        if ($request->wantsJson()) {
            $html = '';
            foreach ($result['posts'] as $post) {
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
                'count' => $result['count']
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
    public function deleteImage(Post $post, $imageId)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $success = $this->imageService->deletePostImage($post, (int) $imageId);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Image deleted successfully' : 'Image not found'
            ], $success ? 200 : 404);
        }

        return redirect()->back()->with(
            $success ? 'success' : 'error',
            $success ? 'Image deleted successfully' : 'Error deleting image'
        );
    }
}
