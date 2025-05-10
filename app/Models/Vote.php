<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'answer_id',
        'value',
        'weight',
    ];

    /**
     * Get the user who cast this vote
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post this vote was cast on (if applicable)
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the answer this vote was cast on (if applicable)
     */
    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
