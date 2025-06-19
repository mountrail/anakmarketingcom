<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomNotification extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'message',
        'link_type',
        'link_value',
        'custom_url',
        'is_pinned',
        'is_active',
        'created_by',
        'use_creator_avatar', // Add this new field
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_active' => 'boolean',
        'use_creator_avatar' => 'boolean',
    ];

    // Add relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActionUrlAttribute()
    {
        switch ($this->link_type) {
            case 'post':
                $post = Post::find($this->link_value);
                return $post ? '/posts/' . $post->slug : '/';
            case 'profile':
                return '/profile/' . $this->link_value;
            case 'custom':
                return $this->custom_url ?: '/';
            default:
                return '/';
        }
    }
    public function getActionUrl()
    {
        switch ($this->link_type) {
            case 'post':
                $post = \App\Models\Post::find($this->link_value);
                return $post ? '/posts/' . $post->slug : null;
            case 'profile':
                return '/profile/' . $this->link_value;
            case 'custom':
                return $this->custom_url;
            default:
                return null;
        }
    }

    // Get the avatar URL to use
    public function getAvatarUrl()
    {
        if ($this->use_creator_avatar && $this->creator) {
            return $this->creator->getProfileImageUrl();
        }

        // Return system/default avatar
        return 'https://ui-avatars.com/api/?name=System&color=6366F1&background=EEF2FF';
    }
}
