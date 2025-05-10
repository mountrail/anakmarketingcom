<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mews\Purifier\Facades\Purifier;

class AnswerController extends Controller
{
    /**
     * Store a newly created answer in storage.
     */
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:5',
        ]);

        // Purify the content before storing to prevent XSS
        $purifiedContent = Purifier::clean($validated['content']);

        $answer = new Answer([
            'user_id' => Auth::id(),
            'content' => $purifiedContent,
        ]);

        $post->answers()->save($answer);

        return redirect()->route('posts.show', $post->id)
            ->with('success', 'Answer posted successfully.');
    }

    /**
     * Toggle the editor's pick status of an answer
     */
    public function toggleEditorsPick(Answer $answer)
    {
        // Only allow authorized users to set editor's pick
        if (!Auth::user()->can('manage-editor-picks')) {
            abort(403);
        }

        $answer->is_editors_pick = !$answer->is_editors_pick;
        $answer->save();

        return redirect()->back()
            ->with('success', 'Editor\'s pick status updated successfully.');
    }
}
