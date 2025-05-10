<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page with posts.
     */
    public function index(Request $request)
    {
        $selectedType = $request->query('type', 'question');

        // Get featured posts (editor's picks)
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers'])
            ->latest()
            ->take(3)
            ->get();

        // Get regular posts filtered by type
        $posts = Post::where('type', $selectedType)
            ->with(['user', 'answers']) // Load relationship data
            ->latest()
            ->paginate(10);

        return view('home.index', compact('posts', 'editorPicks', 'selectedType'));
    }
}
