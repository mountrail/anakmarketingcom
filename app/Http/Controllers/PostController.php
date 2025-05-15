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

        // Get featured posts (editor's picks) filtered by the selected type for the main content
        $typedEditorPicks = Post::featured()
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

    // Other methods remain unchanged
}
