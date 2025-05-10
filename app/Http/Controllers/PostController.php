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
    return view('home.index', compact('selectedType'));
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

        return view('posts.show', compact('post'));
    }
}
