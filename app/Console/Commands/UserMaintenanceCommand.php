<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AnnouncementNotification;
use App\Notifications\BadgeEarnedNotification;
use App\Services\BadgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\OnboardingController;

class UserMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:maintenance
                            {--onboarding : Send onboarding notifications to users who haven\'t received them}
                            {--replace-old : Replace old notifications with wrong URLs}
                            {--badges : Award retroactive badges to existing users}
                            {--cleanup : Delete all user notifications except system ones}
                            {--dry-run : Show what would be done without actually performing actions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprehensive user maintenance: onboarding notifications, badges, and notification cleanup';

    /**
     * System notification types that should not be deleted
     */
    protected $systemNotificationTypes = [
        'App\Notifications\AnnouncementNotification',
        'App\Notifications\BadgeNotification',
        'App\Notifications\BadgeEarnedNotification',
        'App\Notifications\OnboardingNotification',
        'App\Notifications\SystemNotification',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $runOnboarding = $this->option('onboarding');
        $replaceOld = $this->option('replace-old');
        $runBadges = $this->option('badges');
        $runCleanup = $this->option('cleanup');

        // If no specific options are provided, show help
        if (!$runOnboarding && !$replaceOld && !$runBadges && !$runCleanup) {
            $this->showHelp();
            return Command::SUCCESS;
        }

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No actual changes will be made');
            $this->newLine();
        }

        $this->info('🚀 Starting User Maintenance...');
        $this->newLine();

        // Step 1: Clean up notifications if requested
        if ($runCleanup) {
            $this->cleanupUserNotifications($isDryRun);
            $this->newLine();
        }

        // Step 2: Handle old notifications with wrong URLs
        if ($replaceOld) {
            $this->handleOldNotifications($isDryRun);
            $this->newLine();
        }

        // Step 3: Send onboarding notifications
        if ($runOnboarding) {
            $this->sendOnboardingNotifications($isDryRun);
            $this->newLine();
        }

        // Step 4: Award retroactive badges
        if ($runBadges) {
            $this->awardRetroactiveBadges($isDryRun);
            $this->newLine();
        }

        $this->info('🎉 User maintenance completed!');
        return Command::SUCCESS;
    }

    /**
     * Show help information
     */
    private function showHelp()
    {
        $this->info('User Maintenance Command');
        $this->info('========================');
        $this->newLine();
        $this->info('Available options:');
        $this->info('  --onboarding    Send onboarding notifications to users who haven\'t received them');
        $this->info('  --replace-old   Replace old notifications with wrong URLs');
        $this->info('  --badges        Award retroactive badges to existing users');
        $this->info('  --cleanup       Delete all user notifications except system ones');
        $this->info('  --dry-run       Show what would be done without actually performing actions');
        $this->newLine();
        $this->info('Examples:');
        $this->info('  php artisan users:maintenance --onboarding --dry-run');
        $this->info('  php artisan users:maintenance --cleanup --badges');
        $this->info('  php artisan users:maintenance --onboarding --replace-old --badges');
    }

    /**
     * Clean up user notifications except system ones
     * Also removes onboarding notifications from users who have completed onboarding
     */
    private function cleanupUserNotifications($isDryRun)
    {
        $this->info('🧹 Cleaning up user notifications (keeping system notifications)...');

        // Count regular notifications to be deleted
        $regularNotificationsToDelete = DB::table('notifications')
            ->whereNotIn('type', $this->systemNotificationTypes)
            ->count();

        // Count onboarding notifications from completed users
        $completedOnboardingNotifications = $this->getCompletedOnboardingNotifications();
        $onboardingNotificationsToDelete = $completedOnboardingNotifications->count();

        $totalToDelete = $regularNotificationsToDelete + $onboardingNotificationsToDelete;

        if ($totalToDelete === 0) {
            $this->info('✅ No notifications to clean up!');
            return;
        }

        $this->info("📊 Found notifications to delete:");
        $this->info("  - Regular user notifications: {$regularNotificationsToDelete}");
        $this->info("  - Onboarding notifications (completed users): {$onboardingNotificationsToDelete}");
        $this->info("  - Total: {$totalToDelete}");

        // Show what types will be kept
        $this->info('🔒 System notifications that will be kept:');
        foreach ($this->systemNotificationTypes as $type) {
            $count = DB::table('notifications')->where('type', $type)->count();
            $this->info("  - {$type}: {$count} notifications");
        }

        if ($isDryRun) {
            $this->info("🔍 DRY RUN: Would delete {$totalToDelete} notifications");
            $this->showOnboardingCleanupPreview();
            return;
        }

        if (!$this->confirm("Delete {$totalToDelete} notifications?")) {
            $this->info('❌ Notification cleanup cancelled');
            return;
        }

        $this->info('🗑️ Deleting notifications...');

        try {
            $deletedCount = 0;

            // Delete regular user notifications
            $deletedRegular = DB::table('notifications')
                ->whereNotIn('type', $this->systemNotificationTypes)
                ->delete();

            $deletedCount += $deletedRegular;

            // Delete onboarding notifications from completed users
            $deletedOnboarding = $this->deleteCompletedOnboardingNotifications();
            $deletedCount += $deletedOnboarding;

            $this->info("✅ Successfully deleted {$deletedCount} notifications");
            $this->info("  - Regular notifications: {$deletedRegular}");
            $this->info("  - Completed onboarding notifications: {$deletedOnboarding}");

        } catch (\Exception $e) {
            $this->error("❌ Failed to delete notifications: " . $e->getMessage());
            \Log::error('Failed to cleanup user notifications', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get onboarding notifications from users who have completed onboarding
     */
    private function getCompletedOnboardingNotifications()
    {
        return DB::table('notifications')
            ->join('users', 'notifications.notifiable_id', '=', 'users.id')
            ->where('notifications.type', 'App\Notifications\AnnouncementNotification')
            ->where('notifications.data->message', 'like', '%Selesaikan onboarding dan dapatkan badge baru!%')
            ->where('notifications.data->action_url', '/onboarding/checklist')
            ->where('notifications.data->is_pinned', true)
            ->where(function ($query) {
                // User has completed onboarding if they have both name and job_title
                $query->whereNotNull('users.name')
                    ->where('users.name', '!=', '')
                    ->whereNotNull('users.job_title')
                    ->where('users.job_title', '!=', '');
            })
            ->select('notifications.*');
    }

    /**
     * Delete onboarding notifications from users who have completed onboarding
     */
    private function deleteCompletedOnboardingNotifications()
    {
        return DB::table('notifications')
            ->join('users', 'notifications.notifiable_id', '=', 'users.id')
            ->where('notifications.type', 'App\Notifications\AnnouncementNotification')
            ->where('notifications.data->message', 'like', '%Selesaikan onboarding dan dapatkan badge baru!%')
            ->where('notifications.data->action_url', '/onboarding/checklist')
            ->where('notifications.data->is_pinned', true)
            ->where(function ($query) {
                // User has completed onboarding if they have both name and job_title
                $query->whereNotNull('users.name')
                    ->where('users.name', '!=', '')
                    ->whereNotNull('users.job_title')
                    ->where('users.job_title', '!=', '');
            })
            ->delete();
    }

    /**
     * Show preview of onboarding notifications that would be cleaned up
     */
    private function showOnboardingCleanupPreview()
    {
        $notifications = $this->getCompletedOnboardingNotifications()
            ->join('users', 'notifications.notifiable_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.job_title', 'notifications.created_at')
            ->limit(5)
            ->get();

        if ($notifications->count() > 0) {
            $this->info('🔍 Preview of users whose onboarding notifications would be removed:');
            $this->table(
                ['User ID', 'Name', 'Email', 'Job Title', 'Notification Date'],
                $notifications->map(function ($user) {
                    return [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->job_title,
                        $user->created_at
                    ];
                })
            );
        }
    }

    /**
     * Handle old notifications with wrong URLs
     */
    private function handleOldNotifications($isDryRun)
    {
        $this->info('🔍 Searching for users with old onboarding notifications (wrong URL)...');

        // Find users who have the old notification with /onboarding URL
        $usersWithOldNotifications = User::whereHas('notifications', function ($query) {
            $query->where('type', 'App\Notifications\AnnouncementNotification')
                ->where('data->message', 'like', '%Selesaikan onboarding dan dapatkan badge baru!%')
                ->where('data->action_url', '/onboarding') // Old wrong URL
                ->where('data->is_pinned', true);
        })->get();

        $oldNotificationsCount = $usersWithOldNotifications->count();

        if ($oldNotificationsCount === 0) {
            $this->info('✅ No old notifications found!');
            return;
        }

        $this->info("📊 Found {$oldNotificationsCount} users with old onboarding notifications:");
        $this->newLine();

        // Show preview of users with old notifications
        $previewUsers = $usersWithOldNotifications->take(5);
        $this->table(
            ['ID', 'Name', 'Email', 'Old Notifications Count'],
            $previewUsers->map(function ($user) {
                $oldCount = $user->notifications()
                    ->where('type', 'App\Notifications\AnnouncementNotification')
                    ->where('data->message', 'like', '%Selesaikan onboarding dan dapatkan badge baru!%')
                    ->where('data->action_url', '/onboarding')
                    ->where('data->is_pinned', true)
                    ->count();

                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $oldCount
                ];
            })
        );

        if ($oldNotificationsCount > 5) {
            $this->info("... and " . ($oldNotificationsCount - 5) . " more users");
        }

        if ($isDryRun) {
            $this->info("🔍 DRY RUN: Would delete old notifications and send new ones for {$oldNotificationsCount} users");
            return;
        }

        if (!$this->confirm("Delete old notifications and send new ones for {$oldNotificationsCount} users?")) {
            $this->info('❌ Old notification replacement cancelled');
            return;
        }

        $this->info('🗑️ Replacing old notifications...');
        $progressBar = $this->output->createProgressBar($oldNotificationsCount);
        $progressBar->start();

        $replacedCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($usersWithOldNotifications as $user) {
            try {
                // Delete old notifications
                $deletedCount = $user->notifications()
                    ->where('type', 'App\Notifications\AnnouncementNotification')
                    ->where('data->message', 'like', '%Selesaikan onboarding dan dapatkan badge baru!%')
                    ->where('data->action_url', '/onboarding')
                    ->where('data->is_pinned', true)
                    ->delete();

                // Send new notification with correct URL
                $user->notify(new AnnouncementNotification(
                    'Selesaikan onboarding dan dapatkan badge baru!',
                    'Selesaikan onboarding dan dapatkan badge baru! Klik notifikasi ini untuk melanjutkan checklist onboarding kamu',
                    '/onboarding/checklist', // Correct URL
                    true // isPinned = true
                ));

                $replacedCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ];

                \Log::error('Failed to replace onboarding notification for user ' . $user->id, [
                    'user_email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("📊 Old notification replacement results:");
        $this->info("✅ Successfully replaced: {$replacedCount}");

        if ($errorCount > 0) {
            $this->error("❌ Failed: {$errorCount}");
            foreach ($errors as $error) {
                $this->error("- User ID {$error['user_id']} ({$error['email']}): {$error['error']}");
            }
        }
    }

    /**
     * Send onboarding notifications to users who don't have them AND haven't completed onboarding
     * Also send badge notifications for badges they've already earned
     */
    private function sendOnboardingNotifications($isDryRun)
    {
        $this->info('🔍 Searching for users who need onboarding notifications...');

        // Find users who:
        // 1. Don't have the correct onboarding notification
        // 2. Haven't completed onboarding (missing name or job_title)
        $usersNeedingOnboarding = User::whereDoesntHave('notifications', function ($query) {
            $query->where('type', 'App\Notifications\AnnouncementNotification')
                ->where('data->message', 'like', '%Selesaikan onboarding dan dapatkan badge baru!%')
                ->where('data->action_url', '/onboarding/checklist') // Correct URL
                ->where('data->is_pinned', true);
        })->where(function ($query) {
            // User hasn't completed onboarding if they're missing name or job_title
            $query->whereNull('name')
                ->orWhere('name', '')
                ->orWhereNull('job_title')
                ->orWhere('job_title', '');
        })->get();

        $totalUsers = $usersNeedingOnboarding->count();

        if ($totalUsers === 0) {
            $this->info('✅ All users who need onboarding already have notifications!');
            return;
        }

        $this->info("📊 Found {$totalUsers} users who need onboarding notifications:");
        $this->newLine();

        // Show preview of users
        $previewUsers = $usersNeedingOnboarding->take(10);
        $this->table(
            ['ID', 'Name', 'Email', 'Job Title', 'Created At', 'Badges Count'],
            $previewUsers->map(function ($user) {
                return [
                    $user->id,
                    $user->name ?: '(empty)',
                    $user->email,
                    $user->job_title ?: '(empty)',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->badges()->count()
                ];
            })
        );

        if ($totalUsers > 10) {
            $this->info("... and " . ($totalUsers - 10) . " more users");
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info("🔍 DRY RUN: Would send onboarding notifications to {$totalUsers} users");

            // Show badge notification preview
            $usersWithBadges = $usersNeedingOnboarding->filter(function ($user) {
                return $user->badges()->count() > 0;
            });

            if ($usersWithBadges->count() > 0) {
                $this->info("🏆 Would also send badge notifications to {$usersWithBadges->count()} users who have earned badges");
            }

            return;
        }

        // Confirm before sending
        if (!$this->confirm("Send onboarding notifications to {$totalUsers} users?")) {
            $this->info('❌ Operation cancelled');
            return;
        }

        $this->info('📤 Sending onboarding notifications and badge notifications...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar($totalUsers);
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;
        $badgeNotificationCount = 0;
        $errors = [];

        foreach ($usersNeedingOnboarding as $user) {
            try {
                // Send the onboarding notification with correct URL
                $user->notify(new AnnouncementNotification(
                    'Selesaikan onboarding dan dapatkan badge baru!',
                    'Selesaikan onboarding dan dapatkan badge baru! Klik notifikasi ini untuk melanjutkan checklist onboarding kamu',
                    '/onboarding/checklist', // Correct URL
                    true // isPinned = true
                ));

                // Send badge notifications for badges they've already earned
                $earnedBadges = $user->badges()->get();
                foreach ($earnedBadges as $badge) {
                    // Check if user already has a badge notification for this badge
                    $hasNotification = $user->notifications()
                        ->where('type', 'App\Notifications\BadgeEarnedNotification')
                        ->where('data->badge_id', $badge->id)
                        ->exists();

                    if (!$hasNotification) {
                        $user->notify(new BadgeEarnedNotification($badge));
                        $badgeNotificationCount++;
                    }
                }

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ];

                \Log::error('Failed to send onboarding notification to user ' . $user->id, [
                    'user_email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Show results
        $this->info("📊 Notification results:");
        $this->info("✅ Onboarding notifications sent: {$successCount}");
        $this->info("🏆 Badge notifications sent: {$badgeNotificationCount}");

        if ($errorCount > 0) {
            $this->error("❌ Failed: {$errorCount}");
            $this->newLine();

            $this->error("Errors encountered:");
            foreach ($errors as $error) {
                $this->error("- User ID {$error['user_id']} ({$error['email']}): {$error['error']}");
            }
        }
    }

    /**
     * Award retroactive badges to existing users
     */
    private function awardRetroactiveBadges($isDryRun)
    {
        $this->info('🏆 Starting retroactive badge awarding...');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN: Would award retroactive badges to eligible users');
            // You might want to add logic here to show what badges would be awarded
            return;
        }

        try {
            BadgeService::awardRetroactiveBadges();
            $this->info('✅ Retroactive badge awarding completed!');
        } catch (\Exception $e) {
            $this->error('❌ Failed to award retroactive badges: ' . $e->getMessage());
            \Log::error('Failed to award retroactive badges', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
