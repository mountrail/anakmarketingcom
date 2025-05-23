<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostImage;  // Assuming you'll create this model
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
            ->where('type', $selectedType) // Filter by the selected type
            ->with(['user', 'answers'])
            ->latest()
            ->take(3)
            ->get();

        // Get the IDs of featured posts to exclude them from regular posts
        $featuredPostIds = $typedEditorPicks->pluck('id')->toArray();

        // Get regular posts filtered by type, excluding featured posts
        $posts = Post::where('type', $selectedType)
            ->whereNotIn('id', $featuredPostIds) // Exclude featured posts
            ->with(['user', 'answers']) // Load relationship data
            ->latest()
            ->paginate(10);

        // Get editor's picks from both categories for the sidebar
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            // No type filter here, so it will get both questions and discussions
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
        // Share editorPicks for the sidebar (both categories)
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            // No type filter
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

        // Purify the content before storing
        $purifiedContent = Purifier::clean($validated['content']);

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $purifiedContent,
            'type' => $validated['type'],
        ]);

        // Handle the uploaded images if any
        if ($request->has('uploaded_images')) {
            $images = json_decode($request->uploaded_images, true);

            if (!empty($images) && is_array($images)) {
                foreach ($images as $image) {
                    // Create a record for each image
                    $post->images()->create([
                        'url' => $image['url'],
                        'name' => $image['name'] ?? 'Uploaded image',
                    ]);
                }
            }
        }

        return redirect()->route('posts.show', $post->id)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        // Increment view count
        $post->increment('view_count');

        // Load post with its answers, images, and the users who wrote them
        $post->load([
            'answers' => function ($query) {
                $query->latest();
            },
            'answers.user',
            'images' // Load associated images
        ]);

        // Share editorPicks for the sidebar (both categories)
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            // No type filter
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
        // Check if user is owner or has editor/admin role
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Load post with its images
        $post->load('images');

        // Share editorPicks for the sidebar (both categories)
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            // No type filter
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
        // Check if user is owner or has editor/admin role
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:question,discussion',
        ]);

        // Purify the content before storing
        $purifiedContent = Purifier::clean($validated['content']);

        $post->update([
            'title' => $validated['title'],
            'content' => $purifiedContent,
            'type' => $validated['type'],
        ]);

        // Handle the uploaded images if any
        if ($request->has('uploaded_images')) {
            $newImages = json_decode($request->uploaded_images, true);

            // Get current image IDs
            $existingImageIds = $post->images->pluck('id')->toArray();
            $newImageIds = [];

            if (!empty($newImages) && is_array($newImages)) {
                foreach ($newImages as $image) {
                    // Check if this is a new image or existing one
                    if (isset($image['id']) && strpos($image['id'], 'img-') === 0) {
                        // This is a new image from the current session
                        $post->images()->create([
                            'url' => $image['url'],
                            'name' => $image['name'] ?? 'Uploaded image',
                        ]);
                    } else if (isset($image['id'])) {
                        // This is an existing image we want to keep
                        $newImageIds[] = $image['id'];
                    }
                }
            }

            // Remove images that were deleted by the user
            $imagesToDelete = array_diff($existingImageIds, $newImageIds);
            if (!empty($imagesToDelete)) {
                $post->images()->whereIn('id', $imagesToDelete)->delete();
            }
        } else {
            // If no images data provided, remove all images
            $post->images()->delete();
        }

        return redirect()->route('posts.show', $post->id)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        // Check if user is owner or has editor/admin role
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Delete associated images
        $post->images()->delete();

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
        // Check if user has editor/admin role
        if (!Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Toggle featured status
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
}
