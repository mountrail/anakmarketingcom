<?php

namespace App\Http\Controllers;

use App\Models\Post;
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

        // Get featured posts (editor's picks) filtered by the selected type
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->where('type', $selectedType) // Filter by the selected type
            ->with(['user', 'answers'])
            ->latest()
            ->take(3)
            ->get();

        // Get regular posts filtered by type
        $posts = Post::where('type', $selectedType)
            ->with(['user', 'answers']) // Load relationship data
            ->latest()
            ->paginate(10);

        // Share editorPicks with all views
        view()->share('editorPicks', $editorPicks);

        return view('home.index', compact('selectedType', 'posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        // Share editorPicks for the sidebar
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
        // No need to fetch editor's picks here as it redirects
        // without directly returning a view

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

        // Load post with its answers and the users who wrote them
        $post->load([
            'answers' => function ($query) {
                $query->latest();
            },
            'answers.user'
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
        // Check if user is owner or has editor/admin role
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Share editorPicks for the sidebar
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
        // No need to fetch editor's picks here as it redirects
        // without directly returning a view

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

        return redirect()->route('posts.show', $post->id)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        // No need to fetch editor's picks here as it redirects
        // without directly returning a view

        // Check if user is owner or has editor/admin role
        if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $post->delete();

        return redirect()->route('home')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Toggle the featured status of a post (Editor's Pick)
     */
    public function toggleFeatured(Post $post)
    {
        // No need to fetch editor's picks here as it redirects
        // without directly returning a view

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
