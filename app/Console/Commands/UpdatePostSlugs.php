<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostSlugRedirect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePostSlugs extends Command
{
    protected $signature = 'posts:update-slugs {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Update post slugs from user_id/title-id format to title-id format';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $posts = Post::all();
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($posts as $post) {
            $oldSlug = $post->slug;

            // Check if slug is in old format (contains slash)
            if (strpos($oldSlug, '/') === false) {
                $this->line("Skipping post {$post->id}: Already in new format ({$oldSlug})");
                $skippedCount++;
                continue;
            }

            // Generate new slug
            $newSlug = $post->generateSlugWithId($post->title, $post->id);

            $this->info("Post {$post->id}: '{$oldSlug}' -> '{$newSlug}'");

            if (!$isDryRun) {
                try {
                    DB::beginTransaction();

                    // Create redirect for old slug
                    PostSlugRedirect::create([
                        'old_slug' => $oldSlug,
                        'post_id' => $post->id,
                    ]);

                    // Update post slug
                    $post->slug = $newSlug;
                    $post->save();

                    // Update notification URLs
                    $this->updateNotificationUrls($post->id, '/' . $newSlug);

                    DB::commit();
                    $updatedCount++;

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Failed to update post {$post->id}: " . $e->getMessage());
                }
            } else {
                $updatedCount++;
            }
        }

        $this->newLine();
        if ($isDryRun) {
            $this->info("DRY RUN COMPLETE:");
            $this->info("- {$updatedCount} posts would be updated");
            $this->info("- {$skippedCount} posts would be skipped");
            $this->newLine();
            $this->comment("Run without --dry-run to apply changes");
        } else {
            $this->info("MIGRATION COMPLETE:");
            $this->info("- {$updatedCount} posts updated");
            $this->info("- {$skippedCount} posts skipped");
            $this->info("- Old slugs saved as redirects");
        }

        return 0;
    }

    private function updateNotificationUrls($postId, $newUrl)
    {
        // Update PostAnsweredNotification URLs
        DB::table('notifications')
            ->whereRaw("JSON_EXTRACT(data, '$.post_id') = ?", [$postId])
            ->whereRaw("JSON_EXTRACT(data, '$.type') = ?", ['post_answered'])
            ->update([
                'data' => DB::raw("JSON_SET(data, '$.action_url', " . DB::getPdo()->quote($newUrl) . ")")
            ]);

        // Update FollowedUserPostedNotification URLs
        DB::table('notifications')
            ->whereRaw("JSON_EXTRACT(data, '$.post_id') = ?", [$postId])
            ->whereRaw("JSON_EXTRACT(data, '$.type') = ?", ['followed_user_posted'])
            ->update([
                'data' => DB::raw("JSON_SET(data, '$.action_url', " . DB::getPdo()->quote($newUrl) . ")")
            ]);

        // Update announcement notifications that reference this post
        DB::table('notifications')
            ->whereRaw("JSON_EXTRACT(data, '$.post_id') = ?", [$postId])
            ->whereRaw("JSON_EXTRACT(data, '$.type') = ?", ['announcement'])
            ->update([
                'data' => DB::raw("JSON_SET(data, '$.action_url', " . DB::getPdo()->quote($newUrl) . ")")
            ]);
    }
}
