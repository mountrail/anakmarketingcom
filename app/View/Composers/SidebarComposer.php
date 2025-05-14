<?php

namespace App\View\Composers;

use App\Models\Post;
use Illuminate\View\View;

class SidebarComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // Get editor's picks - using the same logic as in PostController
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers'])
            ->latest()
            ->take(5)
            ->get();

        $view->with('editorPicks', $editorPicks);
    }
}
