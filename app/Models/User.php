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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser, HasMedia
{
    use HasFactory, Notifiable, HasRoles, Followable, Follower, InteractsWithMedia;

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
        'onboarding_steps', // Add this line
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
            'onboarding_steps' => 'array', // Add this line to cast JSON to array
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

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_pictures')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png']);
    }

    /**
     * Register media conversions
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->fit(Fit::Crop, 300, 300)  // Use Fit::Crop instead of 'crop'
            ->optimize()
            ->performOnCollections('profile_pictures');

        $this->addMediaConversion('avatar')
            ->width(150)
            ->height(150)
            ->fit(Fit::Crop, 150, 150)  // Use Fit::Crop instead of 'crop'
            ->optimize()
            ->performOnCollections('profile_pictures');
    }

    /**
     * Get the profile image URL
     */
    public function getProfileImageUrl(): string
    {
        // First priority: Check for uploaded profile picture via media library
        $media = $this->getFirstMedia('profile_pictures');

        if ($media) {
            // Return the avatar conversion, fallback to original if conversion doesn't exist
            return $media->hasGeneratedConversion('avatar')
                ? $media->getUrl('avatar')
                : $media->getUrl();
        }

        // Second priority: Check for Google avatar from social login
        if (!empty($this->avatar)) {
            return $this->avatar;
        }

        // Third priority: Check for legacy profile_picture field (if you still use it)
        if (!empty($this->profile_picture)) {
            return asset('storage/' . $this->profile_picture);
        }

        // Final fallback: Generate default avatar using UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the profile image thumbnail URL
     */
    public function getProfileImageThumbUrl(): string
    {
        // First priority: Check for uploaded profile picture via media library
        $media = $this->getFirstMedia('profile_pictures');

        if ($media) {
            return $media->hasGeneratedConversion('thumb')
                ? $media->getUrl('thumb')
                : $media->getUrl();
        }

        // Second priority: Check for Google avatar from social login
        if (!empty($this->avatar)) {
            return $this->avatar;
        }

        // Third priority: Check for legacy profile_picture field (if you still use it)
        if (!empty($this->profile_picture)) {
            return asset('storage/' . $this->profile_picture);
        }

        // Final fallback: Generate default avatar using UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Check if user has any kind of profile picture (uploaded or Google avatar)
     */
    public function hasProfilePicture(): bool
    {
        return $this->hasMedia('profile_pictures') || !empty($this->avatar) || !empty($this->profile_picture);
    }

    /**
     * Check if user has uploaded a custom profile picture
     */
    public function hasUploadedProfilePicture(): bool
    {
        return $this->hasMedia('profile_pictures');
    }

    /**
     * Check if user is using Google avatar
     */
    public function hasGoogleAvatar(): bool
    {
        return !empty($this->avatar) && !$this->hasMedia('profile_pictures');
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
