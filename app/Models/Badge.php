<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'level',
    ];

    /**
     * Users who have earned this badge
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')->withTimestamps()->withPivot('earned_at');
    }

    /**
     * User profile badges
     */
    public function userProfileBadges()
    {
        return $this->hasMany(UserProfileBadge::class);
    }
}
