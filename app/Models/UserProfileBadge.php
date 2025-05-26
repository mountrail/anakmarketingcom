<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use QCod\Gamify\Badge;

class UserProfileBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_id',
        'is_displayed',
        'display_order',
    ];

    protected $casts = [
        'is_displayed' => 'boolean',
    ];

    /**
     * Get the user that owns the profile badge.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the badge associated with this profile badge.
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }
}
