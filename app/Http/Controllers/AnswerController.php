<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Post;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PostAnsweredNotification;
use App\Http\Controllers\OnboardingController;

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

        // Store content as plain text (no HTML purification needed)
        $content = trim($validated['content']);

        // Track if this is the user's first answer ever (for onboarding)
        $isFirstAnswerEver = Auth::user()->answers()->count() === 0;

        $answer = new Answer([
            'user_id' => Auth::id(),
            'content' => $content,
        ]);

        $post->answers()->save($answer);

        // **CHECK FOR "IKUTAN NIMBRUNG" BADGE AFTER ANSWER CREATION**
        $badgeAwarded = BadgeService::checkIkutanNimbrung(Auth::user());

        // **MARK DISCUSSION PARTICIPATION FOR ONBOARDING**
        if ($isFirstAnswerEver) {
            OnboardingController::markDiscussionParticipation(Auth::user());
        }

        // Send notification to the post author if it's not their own answer
        if ($post->user_id !== Auth::id()) {
            $post->user->notify(new PostAnsweredNotification($post, $answer, Auth::user()));
        }

        // **REDIRECT TO BADGE-EARNED PAGE IF BADGE WAS AWARDED**
        if ($badgeAwarded) {
            // Store the post slug in session so we can redirect back after badge page
            session(['return_to_post' => $post->slug]);

            return redirect()->route('onboarding.badge-earned', ['badge' => 'Ikutan Nimbrung'])
                ->with('success', 'Answer posted successfully.');
        }

        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Answer posted successfully.');
    }

    /**
     * Update the specified answer in storage.
     */
    public function update(Request $request, Answer $answer)
    {
        // Check if user is authorized to edit the answer
        if (Auth::id() !== $answer->user_id && !Auth::user()->hasRole(['admin'])) {
            return response()->json([
                'error' => 'You do not have permission to edit this answer.'
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|min:5',
        ]);

        // Store content as plain text (no HTML purification needed)
        $content = trim($validated['content']);

        // Update the answer
        $answer->update([
            'content' => $content,
        ]);

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Answer updated successfully.',
                'content' => $answer->content, // Return the plain text content
            ]);
        }

        // Fallback for non-AJAX requests
        return redirect()->route('posts.show', $answer->post->slug)
            ->with('success', 'Answer updated successfully.');
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

        // Get the post before deleting the answer
        $post = $answer->post;

        // Delete the answer
        $answer->delete();

        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Answer deleted successfully.');
    }
}
