<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'provider',
        'provider_id',
        'phone',
        'password',
        'industry',
        'seniority',
        'company_size',
        'city',
        'profile_picture',
        'title',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
     * 1. Default profile_picture
     * 2. Google avatar
     * 3. Dummy image
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
            return asset('storage/uploads/images/portrait.png');
        }
    }
}
