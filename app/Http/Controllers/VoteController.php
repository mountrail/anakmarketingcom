<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Answer;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoteController extends Controller
{
    /**
     * Vote on a post
     */
    public function votePost(Request $request, Post $post)
    {
        return $this->handleVote($request, $request->user(), null, $post, $request->value);
    }

    /**
     * Vote on an answer
     */
    public function voteAnswer(Request $request, Answer $answer)
    {
        return $this->handleVote($request, $request->user(), $answer, null, $request->value);
    }

    /**
     * Handle the voting logic
     */
    private function handleVote(Request $request, $user, $answer = null, $post = null, $value)
    {
        // Determine if this is a post or answer vote
        $targetType = $post ? 'post' : 'answer';
        $targetId = $post ? $post->id : $answer->id;
        $target = $post ?: $answer;

        // Check if the user has already voted on this target
        $vote = Vote::where('user_id', $user->id)
            ->where($targetType . '_id', $targetId)
            ->first();

        // Determine the weight of the vote (for admin/staff votes)
        $weight = $user->hasRole('admin') || $user->hasRole('staff') ? 5 : 1;

        if ($vote) {
            // User already voted - update or delete
            if ($vote->value == $value) {
                // If clicking the same button, remove the vote
                $vote->delete();
                $message = 'Vote removed';
                $newUserVote = null;
            } else {
                // Change the vote direction
                $vote->value = $value;
                $vote->save();
                $message = 'Vote updated';
                $newUserVote = $vote->value;
            }
        } else {
            // Create new vote
            $vote = Vote::create([
                'user_id' => $user->id,
                $targetType . '_id' => $targetId,
                'value' => $value,
                'weight' => $weight
            ]);
            $message = 'Vote recorded';
            $newUserVote = $vote->value;
        }

        // Calculate the new vote totals for the target
        $upvotes = Vote::where($targetType . '_id', $targetId)
            ->where('value', 1)
            ->sum('weight');

        $downvotes = Vote::where($targetType . '_id', $targetId)
            ->where('value', -1)
            ->sum('weight');

        $score = $upvotes - $downvotes;

        // If this is an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'upvotes' => $upvotes,
                'downvotes' => $downvotes,
                'score' => $score,
                'userVote' => $newUserVote
            ]);
        }

        // For non-AJAX, redirect back
        return redirect()->back()->with('success', $message);
    }
}
