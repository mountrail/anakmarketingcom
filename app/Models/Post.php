<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'type',
        'is_featured',
        'featured_type',
        'view_count',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'vote_score',
        'user_vote',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeQuestions($query)
    {
        return $query->where('type', 'question');
    }

    public function scopeDiscussions($query)
    {
        return $query->where('type', 'discussion');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }


    /**
     * Get the images associated with this post.
     */
    public function images()
    {
        return $this->hasMany(PostImage::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get all votes for this post
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the total score (upvotes - downvotes) for this post
     */
    public function getVoteScoreAttribute()
    {
        $upvotes = $this->votes()->where('value', 1)->sum('weight');
        $downvotes = $this->votes()->where('value', -1)->sum('weight');

        return $upvotes - $downvotes;
    }

    /**
     * Get the current user's vote for this post (1, -1, or null)
     */
    public function getUserVoteAttribute()
    {
        if (!Auth::check()) {
            return null;
        }

        $vote = $this->votes()->where('user_id', Auth::id())->first();
        return $vote ? $vote->value : null;
    }
}
