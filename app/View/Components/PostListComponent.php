<?php

namespace App\View\Components;

use App\Models\Post;
use Illuminate\View\Component;

class PostListComponent extends Component
{
    public $posts;
    public $editorPicks;
    public $selectedType;

    /**
     * Create a new component instance.
     *
     * @param string $selectedType
     * @return void
     */
    public function __construct($selectedType = 'question')
    {
        $this->selectedType = $selectedType;

        // Get featured posts (editor's picks) filtered by the selected type
        $this->editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->where('type', $this->selectedType) // Filter by the selected type
            ->with(['user', 'answers'])
            ->latest()
            ->take(3)
            ->get();

        // Get regular posts filtered by type
        $this->posts = Post::where('type', $this->selectedType)
            ->with(['user', 'answers']) // Load relationship data
            ->latest()
            ->paginate(10);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.post-list');
    }
}
