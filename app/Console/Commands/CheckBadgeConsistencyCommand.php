<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Badge;
use App\Models\UserProfileBadge;
use App\Notifications\BadgeEarnedNotification;
use App\Services\BadgeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckBadgeConsistencyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:check-consistency
                            {--user= : Check specific user ID}
                            {--dry-run : Show what would be done without making changes}
                            {--fix-notifications : Only fix missing notifications without retroactive checks}
                            {--retroactive-only : Only perform retroactive badge checks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and fix badge consistency - ensure users with badges have notifications and vice versa, plus retroactive badge awarding';

    /**
     * Statistics tracking
     */
    private $stats = [
        'users_checked' => 0,
        'badges_added' => 0,
        'notifications_added' => 0,
        'notifications_removed' => 0,
        'profile_badges_created' => 0,
        'errors' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting badge consistency check...');

        $isDryRun = $this->option('dry-run');
        $fixNotificationsOnly = $this->option('fix-notifications');
        $retroactiveOnly = $this->option('retroactive-only');
        $specificUserId = $this->option('user');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get users to check
        $users = $this->getUsers($specificUserId);

        if ($users->isEmpty()) {
            $this->error('No users found to check.');
            return 1;
        }

        $this->info("Checking {$users->count()} users...");

        // Create progress bar
        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            try {
                $this->checkUserBadgeConsistency($user, $isDryRun, $fixNotificationsOnly, $retroactiveOnly);
                $this->stats['users_checked']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                Log::error("Error checking user {$user->id}: " . $e->getMessage());

                if ($this->getOutput()->isVerbose()) {
                    $this->error("\nError checking user {$user->id}: " . $e->getMessage());
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // Display results
        $this->displayResults($isDryRun);

        return 0;
    }

    /**
     * Get users to check based on options
     */
    private function getUsers($specificUserId)
    {
        if ($specificUserId) {
            $user = User::find($specificUserId);
            return $user ? collect([$user]) : collect();
        }

        return User::with(['badges', 'notifications'])->get();
    }

    /**
     * Check badge consistency for a specific user
     */
    private function checkUserBadgeConsistency(User $user, bool $isDryRun, bool $fixNotificationsOnly, bool $retroactiveOnly)
    {
        if (!$retroactiveOnly) {
            $this->checkBadgeNotificationConsistency($user, $isDryRun);
        }

        if (!$fixNotificationsOnly) {
            $this->performRetroactiveBadgeCheck($user, $isDryRun);
        }
    }

    /**
     * Check if user's badges match their notifications
     */
    private function checkBadgeNotificationConsistency(User $user, bool $isDryRun)
    {
        // Get user's earned badges
        $earnedBadges = $user->badges()->get();

        // Get user's badge earned notifications
        $badgeNotifications = $user->notifications()
            ->where('type', BadgeEarnedNotification::class)
            ->whereJsonContains('data->type', 'badge_earned')
            ->get();

        // Create a map of badge IDs from notifications
        $notificationBadgeIds = $badgeNotifications->pluck('data.badge_id')->filter()->toArray();

        // Check for badges without notifications
        foreach ($earnedBadges as $badge) {
            if (!in_array($badge->id, $notificationBadgeIds)) {
                $this->addMissingNotification($user, $badge, $isDryRun);
            }

            // Ensure UserProfileBadge entry exists
            $this->ensureProfileBadgeEntry($user, $badge, $isDryRun);
        }

        // Check for notifications without badges (orphaned notifications)
        $earnedBadgeIds = $earnedBadges->pluck('id')->toArray();

        foreach ($badgeNotifications as $notification) {
            $badgeId = $notification->data['badge_id'] ?? null;

            if ($badgeId && !in_array($badgeId, $earnedBadgeIds)) {
                $this->removeOrphanedNotification($user, $notification, $isDryRun);
            }
        }
    }

    /**
     * Perform retroactive badge checking
     */
    private function performRetroactiveBadgeCheck(User $user, bool $isDryRun)
    {
        if (!$isDryRun) {
            // Use the existing BadgeService method for retroactive checking
            $beforeBadgeCount = $user->badges()->count();
            BadgeService::checkAllBadgesForUser($user);
            $afterBadgeCount = $user->fresh()->badges()->count();

            $newBadgesCount = $afterBadgeCount - $beforeBadgeCount;
            if ($newBadgesCount > 0) {
                $this->stats['badges_added'] += $newBadgesCount;
            }
        } else {
            // In dry run mode, simulate the checks
            $this->simulateRetroactiveBadgeCheck($user);
        }
    }

    /**
     * Simulate retroactive badge check for dry run
     */
    private function simulateRetroactiveBadgeCheck(User $user)
    {
        $potentialBadges = [];

        // Check Perkenalkan Saya
        if (!$user->hasBadge('Perkenalkan Saya') && !empty($user->name) && !empty($user->job_title)) {
            $potentialBadges[] = 'Perkenalkan Saya';
        }

        // Check Break the Ice
        if (!$user->hasBadge('Break the Ice') && $user->posts()->count() >= 1) {
            $potentialBadges[] = 'Break the Ice';
        }

        // Check Ikutan Nimbrung
        if (!$user->hasBadge('Ikutan Nimbrung') && $user->answers()->count() >= 1) {
            $potentialBadges[] = 'Ikutan Nimbrung';
        }

        // Check Marketers Onboard!
        if (!$user->hasBadge('Marketers Onboard!')) {
            $hasBasicProfile = !empty($user->name) && !empty($user->job_title);
            $hasAccessedNotifications = $this->hasAccessedNotificationCenter($user);
            $hasFirstPost = $user->posts()->count() > 0;
            $hasFirstAnswer = $user->answers()->count() > 0;
            $hasFirstVote = $user->votes()->count() > 0;
            $hasParticipatedInDiscussion = $hasFirstAnswer || $hasFirstVote;
            $hasFollowedUser = $user->followings()->count() > 0;

            if ($hasBasicProfile && $hasAccessedNotifications && $hasFirstPost && $hasParticipatedInDiscussion && $hasFollowedUser) {
                $potentialBadges[] = 'Marketers Onboard!';
            }
        }

        $this->stats['badges_added'] += count($potentialBadges);

        if (!empty($potentialBadges) && $this->getOutput()->isVerbose()) {
            $this->line("User {$user->id} would earn: " . implode(', ', $potentialBadges));
        }
    }

    /**
     * Check if user has accessed notification center
     */
    private function hasAccessedNotificationCenter($user): bool
    {
        if ($user->notifications()->whereNotNull('read_at')->exists()) {
            return true;
        }

        return $user->onboarding_steps &&
            in_array('accessed_notifications', json_decode($user->onboarding_steps, true) ?? []);
    }

    /**
     * Add missing notification for a badge
     */
    private function addMissingNotification(User $user, Badge $badge, bool $isDryRun)
    {
        if (!$isDryRun) {
            $user->notify(new BadgeEarnedNotification($badge));
        }

        $this->stats['notifications_added']++;

        if ($this->getOutput()->isVerbose()) {
            $action = $isDryRun ? 'Would add' : 'Added';
            $this->line("$action missing notification for badge '{$badge->name}' to user {$user->id}");
        }
    }

    /**
     * Remove orphaned notification
     */
    private function removeOrphanedNotification(User $user, $notification, bool $isDryRun)
    {
        if (!$isDryRun) {
            $notification->delete();
        }

        $this->stats['notifications_removed']++;

        if ($this->getOutput()->isVerbose()) {
            $badgeName = $notification->data['badge_name'] ?? 'Unknown';
            $action = $isDryRun ? 'Would remove' : 'Removed';
            $this->line("$action orphaned notification for badge '{$badgeName}' from user {$user->id}");
        }
    }

    /**
     * Ensure UserProfileBadge entry exists
     */
    private function ensureProfileBadgeEntry(User $user, Badge $badge, bool $isDryRun)
    {
        if (!$isDryRun) {
            $created = UserProfileBadge::firstOrCreate([
                'user_id' => $user->id,
                'badge_id' => $badge->id,
            ], [
                'is_displayed' => false,
                'display_order' => null,
            ]);

            if ($created->wasRecentlyCreated) {
                $this->stats['profile_badges_created']++;

                if ($this->getOutput()->isVerbose()) {
                    $this->line("Created missing UserProfileBadge entry for badge '{$badge->name}' and user {$user->id}");
                }
            }
        }
    }

    /**
     * Display the results of the consistency check
     */
    private function displayResults(bool $isDryRun)
    {
        $this->newLine();
        $this->info('=== Badge Consistency Check Results ===');

        $action = $isDryRun ? 'Would be' : 'Were';

        $this->table(
            ['Metric', 'Count'],
            [
                ['Users Checked', $this->stats['users_checked']],
                ['Badges ' . ($isDryRun ? 'Would Be Added' : 'Added'), $this->stats['badges_added']],
                ['Notifications ' . ($isDryRun ? 'Would Be Added' : 'Added'), $this->stats['notifications_added']],
                ['Notifications ' . ($isDryRun ? 'Would Be Removed' : 'Removed'), $this->stats['notifications_removed']],
                ['Profile Badge Entries ' . ($isDryRun ? 'Would Be Created' : 'Created'), $this->stats['profile_badges_created']],
                ['Errors', $this->stats['errors']],
            ]
        );

        if ($this->stats['errors'] > 0) {
            $this->warn("There were {$this->stats['errors']} errors. Check the logs for details.");
        }

        if ($isDryRun && ($this->stats['badges_added'] > 0 || $this->stats['notifications_added'] > 0 || $this->stats['notifications_removed'] > 0)) {
            $this->info('Run without --dry-run to apply these changes.');
        }

        if (!$isDryRun && ($this->stats['badges_added'] > 0 || $this->stats['notifications_added'] > 0)) {
            $this->info('Badge consistency check completed successfully!');
        }
    }
}
