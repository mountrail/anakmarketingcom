<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\PostImageService;
use App\Services\PostLoadingService;
use App\Services\PostValidationService;
use App\Services\PostNotificationService;
use App\Services\PostAuthorizationService;
use App\Services\PostRedirectService;
use App\Services\PostViewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\OnboardingController;

class PostController extends Controller
{
    protected PostImageService $imageService;
    protected PostLoadingService $loadingService;
    protected PostValidationService $validationService;
    protected PostNotificationService $notificationService;
    protected PostAuthorizationService $authorizationService;
    protected PostRedirectService $redirectService;
    protected PostViewService $viewService;

    public function __construct(
        PostImageService $imageService,
        PostLoadingService $loadingService,
        PostValidationService $validationService,
        PostNotificationService $notificationService,
        PostAuthorizationService $authorizationService,
        PostRedirectService $redirectService,
        PostViewService $viewService
    ) {
        $this->imageService = $imageService;
        $this->loadingService = $loadingService;
        $this->validationService = $validationService;
        $this->notificationService = $notificationService;
        $this->authorizationService = $authorizationService;
        $this->redirectService = $redirectService;
        $this->viewService = $viewService;
    }

    /**
     * Check if authenticated user needs onboarding and redirect if necessary
     */
    private function checkOnboardingRequired()
    {
        if (auth()->check() && OnboardingController::shouldShowOnboarding(auth()->user())) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'redirect' => route('onboarding.basic-profile'),
                    'message' => 'Silakan lengkapi profil dasar Anda terlebih dahulu.'
                ], 302);
            }

            return redirect()->route('onboarding.basic-profile')
                ->with('info', 'Silakan lengkapi profil dasar Anda terlebih dahulu.');
        }
        return null;
    }
    public function index(Request $request)
    {
        $onboardingCheck = $this->checkOnboardingRequired();
        if ($onboardingCheck) {
            return $onboardingCheck;
        }

        // Determine type from route or default to question
        $selectedType = $request->route()->parameter('type') ?? $request->query('type', 'question');

        $data = $this->loadingService->getPostsForIndex($selectedType, 10);

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
        return view('posts.create');
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validationService->validateStore($request);
            $purifiedContent = Purifier::clean($validated['content']);

            $post = Post::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'content' => $purifiedContent,
                'type' => $validated['type'],
            ]);

            $this->imageService->handlePostImages($request, $post);
            $badgeAwarded = BadgeService::checkBreakTheIce(Auth::user());
            $this->notificationService->notifyFollowersOfNewPost($post, auth()->user());

            $successMessage = $validated['type'] === 'question' ? 'Pertanyaan berhasil dibuat!' : 'Diskusi berhasil dibuat!';

            // Badge is awarded silently in background for first post

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
        $onboardingCheck = $this->checkOnboardingRequired();
        if ($onboardingCheck) {
            return $onboardingCheck;
        }

        // Handle redirects first
        $redirect = $this->redirectService->handlePostRedirect($slug);
        if ($redirect) {
            return $redirect;
        }

        // Get the post
        $post = $this->redirectService->getPostBySlug($slug);
        if (!$post) {

            // Return custom 404 view for posts
            return response()->view('errors.post-not-found', [], 404);
        }

        // Record initial view (don't increment yet)
        $this->viewService->recordInitialView($post);

        $post = $this->viewService->loadPostForDisplay($post);

        return view('posts.show', compact('post'));
    }
    /**
     * Increment view count after user has viewed for 45 seconds (AJAX endpoint)
     */
    public function incrementView(Post $post)
    {
        $result = $this->viewService->incrementViewCount($post);

        return response()->json($result);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        $this->authorizationService->authorizeOrAbort(
            $this->authorizationService->canEdit($post, Auth::user())
        );

        $post->load('images');

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Post $post)
    {
        $this->authorizationService->authorizeOrAbort(
            $this->authorizationService->canEdit($post, Auth::user())
        );

        try {
            $validated = $this->validationService->validateUpdate($request);
            $purifiedContent = Purifier::clean($validated['content']);

            $post->update([
                'title' => $validated['title'],
                'content' => $purifiedContent,
                'type' => $validated['type'],
            ]);

            $this->imageService->handleImageUpdates($request, $post);

            $successMessage = $validated['type'] === 'question' ? 'Pertanyaan berhasil diperbarui!' : 'Diskusi berhasil diperbarui!';
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
        $this->authorizationService->authorizeOrAbort(
            $this->authorizationService->canDelete($post, Auth::user())
        );

        try {
            $this->imageService->deleteAllPostImages($post);
            $post->slugRedirects()->delete();
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
     * Toggle the featured status of a post (Editor's Pick)
     */
    public function toggleFeatured(Post $post)
    {
        $this->authorizationService->authorizeOrAbort(
            $this->authorizationService->canManageFeatured(Auth::user())
        );

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
    public function loadMore(Request $request)
    {
        $selectedType = $request->get('type', 'question');
        $page = $request->get('page', 2);

        // Get featured posts for first page only
        if ($page == 1) {
            $typedEditorPicks = Post::featured()
                ->where('featured_type', '!=', 'none')
                ->where('type', $selectedType)
                ->with(['user', 'answers', 'images'])
                ->latest()
                ->get();
            $featuredPostIds = $typedEditorPicks->pluck('id')->toArray();
        } else {
            $typedEditorPicks = collect();
            $featuredPostIds = Post::featured()
                ->where('featured_type', '!=', 'none')
                ->where('type', $selectedType)
                ->pluck('id')
                ->toArray();
        }

        $posts = Post::where('type', $selectedType)
            ->whereNotIn('id', $featuredPostIds)
            ->with(['user', 'answers', 'images'])
            ->latest()
            ->paginate(10, ['*'], 'page', $page);

        if ($request->ajax()) {
            $html = '';
            foreach ($posts as $post) {
                $html .= view('components.post-item', [
                    'post' => $post,
                    'showVoteScore' => false,
                    'showCommentCount' => true,
                    'showShare' => true
                ])->render();
            }

            return response()->json([
                'html' => $html,
                'hasMore' => $posts->hasMorePages(),
                'nextPage' => $posts->currentPage() + 1
            ]);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
    /**
     * Load more posts for a specific user (AJAX)
     */
    public function loadUserPosts(Request $request, User $user)
    {
        $onboardingCheck = $this->checkOnboardingRequired();
        if ($onboardingCheck) {
            return $onboardingCheck;
        }

        $result = $this->loadingService->loadUserPosts($request, $user);

        if ($request->wantsJson()) {
            $html = $this->viewService->renderPostItems($result['posts']);

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
        $this->authorizationService->authorizeOrAbort(
            $this->authorizationService->canSendAnnouncements(Auth::user())
        );

        $validated = $this->validationService->validateAnnouncement($request);

        $this->notificationService->sendPostAnnouncement(
            $post,
            $validated['message'],
            $request->boolean('is_pinned'),
            Auth::user()
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
        $this->authorizationService->authorizeOrAbort(
            $this->authorizationService->canEdit($post, Auth::user())
        );

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
