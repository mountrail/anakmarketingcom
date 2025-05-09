<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
        ]);

        return redirect()->route('posts.show', $post->id)
            ->with('success', 'Post created successfully.');
    }

    public function show(Post $post)
    {
        // Increment view count
        $post->increment('view_count');

        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $this->auth('update', $post);

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $this->auth('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:question,discussion',
        ]);

        $post->update($validated);

        return redirect()->route('posts.show', $post->id)
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $this->auth('delete', $post);

        $post->delete();

        return redirect()->route('home.index')
            ->with('success', 'Post deleted successfully.');
    }
}
