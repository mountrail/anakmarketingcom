<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Overtrue\LaravelFollow\Traits\Followable;
use Overtrue\LaravelFollow\Traits\Follower;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable, HasRoles, Followable, Follower;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'industry',
        'seniority',
        'company_size',
        'city',
        'title',
        'google_id',
        'avatar',
        'provider',
        'provider_id',
        'profile_picture',
        'bio',
        'job_title',
        'company',
        'reputation',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'reputation' => 'integer',
        ];
    }

    public function hasVerifiedEmail()
    {
        return $this->provider === 'google' || $this->email_verified_at !== null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }
        return false;
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Badges earned by this user
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')->withTimestamps()->withPivot('earned_at');
    }

    /**
     * Check if user has a specific badge
     */
    public function hasBadge($badge)
    {
        if (is_string($badge)) {
            return $this->badges()->where('name', $badge)->exists();
        }

        if (is_numeric($badge)) {
            return $this->badges()->where('badge_id', $badge)->exists();
        }

        if ($badge instanceof Badge) {
            return $this->badges()->where('badge_id', $badge->id)->exists();
        }

        return false;
    }

    /**
     * Give a badge to the user
     */
    public function giveBadge($badge)
    {
        if (is_string($badge)) {
            $badge = Badge::where('name', $badge)->first();
        }

        if (!$badge instanceof Badge) {
            return false;
        }

        if ($this->hasBadge($badge)) {
            return false;
        }

        $this->badges()->attach($badge->id, ['earned_at' => now()]);
        return true;
    }

    /**
     * Get the user's profile badges.
     */
    public function profileBadges()
    {
        return $this->hasMany(UserProfileBadge::class);
    }

    /**
     * Get the user's displayed badges.
     */
    public function displayedBadges()
    {
        return $this->profileBadges()->where('is_displayed', true)->orderBy('display_order');
    }

    public function getProfileImageUrl()
    {
        if (!empty($this->profile_picture)) {
            return asset('storage/' . $this->profile_picture);
        } elseif (!empty($this->avatar)) {
            return $this->avatar;
        } else {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
        }
    }

    public function isFollowedBy($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return false;
        }

        return $user->isFollowing($this);
    }

    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    public function getFollowingCountAttribute()
    {
        return $this->followings()->count();
    }
}
