<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendOnboardingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-onboarding {--dry-run : Show what would be done without actually sending notifications} {--replace-old : Replace old notifications with wrong URLs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send onboarding notifications to users who haven\'t received them yet, and optionally replace old ones with wrong URLs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $replaceOld = $this->option('replace-old');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No notifications will be sent or deleted');
            $this->newLine();
        }

        // Step 1: Handle old notifications with wrong URLs
        if ($replaceOld) {
            $this->handleOldNotifications($isDryRun);
            $this->newLine();
        }

        // Step 2: Send notifications to users who don't have the correct onboarding notification
        $this->sendOnboardingNotifications($isDryRun);

        return Command::SUCCESS;
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
     * Send onboarding notifications to users who don't have them
     */
    private function sendOnboardingNotifications($isDryRun)
    {
        $this->info('🔍 Searching for users who haven\'t received onboarding notifications...');

        // Find users who don't have the correct onboarding notification
        $usersWithoutOnboarding = User::whereDoesntHave('notifications', function ($query) {
            $query->where('type', 'App\Notifications\AnnouncementNotification')
                ->where('data->message', 'like', '%Selesaikan onboarding dan dapatkan badge baru!%')
                ->where('data->action_url', '/onboarding/checklist') // Correct URL
                ->where('data->is_pinned', true);
        })->get();

        $totalUsers = $usersWithoutOnboarding->count();

        if ($totalUsers === 0) {
            $this->info('✅ All users already have correct onboarding notifications!');
            return;
        }

        $this->info("📊 Found {$totalUsers} users without correct onboarding notifications:");
        $this->newLine();

        // Show preview of users
        $previewUsers = $usersWithoutOnboarding->take(10);
        $this->table(
            ['ID', 'Name', 'Email', 'Created At'],
            $previewUsers->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at->format('Y-m-d H:i:s')
                ];
            })
        );

        if ($totalUsers > 10) {
            $this->info("... and " . ($totalUsers - 10) . " more users");
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info("🔍 DRY RUN: Would send onboarding notifications to {$totalUsers} users");
            return;
        }

        // Confirm before sending
        if (!$this->confirm("Send onboarding notifications to {$totalUsers} users?")) {
            $this->info('❌ Operation cancelled');
            return;
        }

        $this->info('📤 Sending onboarding notifications...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar($totalUsers);
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($usersWithoutOnboarding as $user) {
            try {
                // Send the onboarding notification with correct URL
                $user->notify(new AnnouncementNotification(
                    'Selesaikan onboarding dan dapatkan badge baru!',
                    'Selesaikan onboarding dan dapatkan badge baru! Klik notifikasi ini untuk melanjutkan checklist onboarding kamu',
                    '/onboarding/checklist', // Correct URL
                    true // isPinned = true
                ));

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
        $this->info("📊 New notification results:");
        $this->info("✅ Successfully sent: {$successCount}");

        if ($errorCount > 0) {
            $this->error("❌ Failed: {$errorCount}");
            $this->newLine();

            $this->error("Errors encountered:");
            foreach ($errors as $error) {
                $this->error("- User ID {$error['user_id']} ({$error['email']}): {$error['error']}");
            }
        }

        $this->newLine();
        $this->info('🎉 Onboarding notification sending completed!');
    }
}
