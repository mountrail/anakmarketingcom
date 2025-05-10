<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'is_editors_pick',
    ];

    protected $casts = [
        'is_editors_pick' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'vote_score',
        'user_vote',
    ];

    /**
     * Get the post that owns the answer.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who created the answer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all votes for this answer
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the total score (upvotes - downvotes) for this answer
     */
    public function getVoteScoreAttribute()
    {
        $upvotes = $this->votes()->where('value', 1)->sum('weight');
        $downvotes = $this->votes()->where('value', -1)->sum('weight');

        return $upvotes - $downvotes;
    }

    /**
     * Get the current user's vote for this answer (1, -1, or null)
     */
    public function getUserVoteAttribute()
    {
        if (!Auth::check()) {
            return null;
        }

        $vote = $this->votes()->where('user_id', Auth::id())->first();
        return $vote ? $vote->value : null;
    }

    /**
     * Scope a query to only include featured answers.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
