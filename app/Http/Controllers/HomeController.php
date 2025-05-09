<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage with filtered posts.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'question'); // Default to questions

        $posts = Post::where('type', $type)
                    ->latest()
                    ->paginate(10);

        // Get featured posts for the editor's picks section
        $editorPicks = Post::where('is_featured', true)
                         ->latest()
                         ->take(3)
                         ->get();

        return view('home.index', [
            'posts' => $posts,
            'editorPicks' => $editorPicks,
            'selectedType' => $type
        ]);
    }
}
