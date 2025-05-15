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
        // Using Spatie's permission system instead of a direct can check
        if (!Auth::user()->hasRole(['editor', 'admin'])) {
            abort(403, 'You do not have permission to manage editor picks');
        }

        $answer->is_editors_pick = !$answer->is_editors_pick;
        $answer->save();

        return redirect()->back()
            ->with('success', 'Editor\'s pick status updated successfully.');
    }

    /**
     * Remove the specified answer from storage.
     */
    public function destroy(Answer $answer)
    {
        // Check if user is authorized to delete the answer
        if (Auth::id() !== $answer->user_id && !Auth::user()->hasRole(['admin'])) {
            abort(403, 'You do not have permission to delete this answer');
        }

        // Get the post ID before deleting the answer
        $postId = $answer->post_id;

        // Delete the answer
        $answer->delete();

        return redirect()->route('posts.show', $postId)
            ->with('success', 'Answer deleted successfully.');
    }
}
