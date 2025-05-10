<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(10);
        return view('home.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

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

    public function show(Post $post)
    {
        // Increment view count
        $post->increment('view_count');

        // If you want to purify on display instead of storage
        // You can comment this out if you're already purifying in the blade template
        // $post->content = Purifier::clean($post->content);

        return view('posts.show', compact('post'));
    }
}
