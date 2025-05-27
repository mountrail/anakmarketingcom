<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
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

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Generate slug when creating
        static::creating(function ($post) {
            // For new posts, we'll set a temporary slug and update it after creation
            $post->slug = $post->generateTempSlug($post->title);
        });

        // Update slug after creation to include ID
        static::created(function ($post) {
            $post->updateSlugWithId();
        });

        // Handle slug changes when updating
        static::updating(function ($post) {
            if ($post->isDirty('title')) {
                $newSlug = $post->generateSlugWithId($post->title, $post->id);

                // If slug would change, save the old one for redirect
                if ($post->slug !== $newSlug) {
                    // Store old slug for redirect
                    PostSlugRedirect::create([
                        'old_slug' => $post->slug,
                        'post_id' => $post->id,
                    ]);

                    // Update the slug
                    $post->slug = $newSlug;
                }
            }
        });

        // Update related data when slug changes
        static::updated(function ($post) {
            if ($post->isDirty('slug')) {
                $post->updateRelatedDataAfterSlugChange();
            }
        });
    }

    /**
     * Generate a temporary slug for new posts (before we have an ID)
     */
    public function generateTempSlug($title)
    {
        $baseSlug = Str::slug($title);

        if (empty($baseSlug)) {
            $baseSlug = 'post';
        }

        // Add a temporary suffix to avoid collisions
        $slug = $baseSlug . '-temp-' . time() . '-' . mt_rand(1000, 9999);

        return $slug;
    }

    /**
     * Generate slug using title-id format
     */
    public function generateSlugWithId($title, $id)
    {
        $baseSlug = Str::slug($title);

        if (empty($baseSlug)) {
            $baseSlug = 'post';
        }

        $slug = $baseSlug . '-' . $id;

        // Ensure uniqueness (though ID should make it unique)
        $counter = 1;
        $originalSlug = $slug;

        while (
            static::where('slug', $slug)
                ->where('id', '!=', $id)
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Update slug after creation (to include ID)
     */
    public function updateSlugWithId()
    {
        if ($this->id) {
            $newSlug = $this->generateSlugWithId($this->title, $this->id);

            // Update directly in database to avoid triggering events again
            DB::table('posts')
                ->where('id', $this->id)
                ->update(['slug' => $newSlug]);

            // Update the model instance
            $this->slug = $newSlug;
        }
    }

    /**
     * Update all related data when slug changes
     */
    protected function updateRelatedDataAfterSlugChange()
    {
        $newUrl = '/posts/' . $this->slug;

        // Update notification URLs
        $this->updateNotificationUrls($newUrl);

        // Update any cached URLs or other references
        $this->updateCachedReferences($newUrl);
    }

    /**
     * Update notification URLs when slug changes
     */
    protected function updateNotificationUrls($newUrl)
    {
        // Update PostAnsweredNotification URLs
        DB::table('notifications')
            ->where('data->post_id', $this->id)
            ->where('data->type', 'post_answered')
            ->update([
                'data' => DB::raw("JSON_SET(data, '$.action_url', '{$newUrl}')")
            ]);

        // Update FollowedUserPostedNotification URLs
        DB::table('notifications')
            ->where('data->post_id', $this->id)
            ->where('data->type', 'followed_user_posted')
            ->update([
                'data' => DB::raw("JSON_SET(data, '$.action_url', '{$newUrl}')")
            ]);

        // Update announcement notifications that reference this post
        DB::table('notifications')
            ->where('data->post_id', $this->id)
            ->where('data->type', 'announcement')
            ->update([
                'data' => DB::raw("JSON_SET(data, '$.action_url', '{$newUrl}')")
            ]);
    }

    /**
     * Update any cached references or other data that depends on the slug
     */
    protected function updateCachedReferences($newUrl)
    {
        // Clear any relevant caches
        if (function_exists('cache')) {
            cache()->forget('post_' . $this->id);
            cache()->forget('post_url_' . $this->id);
        }

        // Update any other systems that might cache post URLs
        // Add your custom logic here if needed
    }

    /**
     * Get route key name for model binding
     * This determines which field Laravel uses for route binding by default
     */
    public function getRouteKeyName()
    {
        // We'll handle this dynamically in resolveRouteBinding instead
        return 'id';
    }

    /**
     * Resolve route binding with support for both ID and slug
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If it's numeric, treat it as an ID (for form submissions)
        if (is_numeric($value)) {
            return $this->where('id', $value)->first();
        }

        // Otherwise, treat it as a slug (for display URLs)
        $post = $this->where('slug', $value)->first();

        if ($post) {
            return $post;
        }

        // Check if it's an old slug that needs redirecting
        $redirect = PostSlugRedirect::where('old_slug', $value)->first();
        if ($redirect && $redirect->post) {
            // This will be handled by the controller with a 301 redirect
            return null; // Let controller handle the redirect
        }

        return null;
    }

    // ... rest of your existing methods remain the same ...

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

    public function images()
    {
        return $this->hasMany(PostImage::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function getVoteScoreAttribute()
    {
        $upvotes = $this->votes()->where('value', 1)->sum('weight');
        $downvotes = $this->votes()->where('value', -1)->sum('weight');

        return $upvotes - $downvotes;
    }

    public function getUserVoteAttribute()
    {
        if (!Auth::check()) {
            return null;
        }

        $vote = $this->votes()->where('user_id', Auth::id())->first();
        return $vote ? $vote->value : null;
    }

    /**
     * Get the URL for this post
     */
    public function getUrlAttribute()
    {
        return route('posts.show', $this->slug);
    }

    /**
     * Redirect relationships for old slugs
     */
    public function slugRedirects()
    {
        return $this->hasMany(PostSlugRedirect::class);
    }
}
