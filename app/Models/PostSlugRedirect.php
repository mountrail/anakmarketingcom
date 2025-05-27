<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostSlugRedirect extends Model
{
    protected $fillable = [
        'old_slug',
        'post_id',
    ];

    /**
     * Get the post that this redirect points to
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Clean up old redirects (optional maintenance method)
     * You can call this periodically to remove very old redirects
     */
    public static function cleanupOldRedirects($daysOld = 365)
    {
        static::where('created_at', '<', now()->subDays($daysOld))->delete();
    }
}
