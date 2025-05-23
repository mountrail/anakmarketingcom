<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Override the hasVerifiedEmail method to always return true for Google users
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return $this->provider === 'google' || $this->email_verified_at !== null;
    }

    /**
     * Determine if the user can access Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Option 1: Check if user has admin role
        if ($this->hasRole('admin')) {
            return true;
        }

        // Option 2: Check specific email domains (if needed)
        // if (str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail()) {
        //     return true;
        // }

        // Option 3: Check specific user IDs (for development/testing)
        // if (in_array($this->id, [1, 2, 3])) { // Replace with your admin user IDs
        //     return true;
        // }

        return false;
    }

    /**
     * Get the user's posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the user's answers.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get the user's votes.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the user's profile picture URL with priority:
     * 1. Custom uploaded profile picture
     * 2. Google avatar
     * 3. UI Avatars fallback
     *
     * @return string
     */
    public function getProfileImageUrl()
    {
        if (!empty($this->profile_picture)) {
            return asset('storage/' . $this->profile_picture);
        } elseif (!empty($this->avatar)) {
            return $this->avatar;
        } else {
            // Default avatar using UI Avatars
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
        }
    }
}
