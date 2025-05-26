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
    protected $signature = 'notifications:send-onboarding {--dry-run : Show what would be done without actually sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send onboarding notifications to users who haven\'t received them yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No notifications will be sent');
            $this->newLine();
        }

        $this->info('🔍 Searching for users who haven\'t received onboarding notifications...');

        // Find users who don't have the onboarding notification
        // We'll check for notifications with the specific message content
        $usersWithoutOnboarding = User::whereDoesntHave('notifications', function ($query) {
            $query->where('type', 'App\Notifications\AnnouncementNotification')
                ->where('data->message', 'like', '%Selesaikan onboarding dan dapatkan badge baru!%')
                ->where('data->action_url', '/onboarding')
                ->where('data->is_pinned', true);
        })->get();

        $totalUsers = $usersWithoutOnboarding->count();

        if ($totalUsers === 0) {
            $this->info('✅ All users already have onboarding notifications!');
            return Command::SUCCESS;
        }

        $this->info("📊 Found {$totalUsers} users without onboarding notifications:");
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
            return Command::SUCCESS;
        }

        // Confirm before sending
        if (!$this->confirm("Send onboarding notifications to {$totalUsers} users?")) {
            $this->info('❌ Operation cancelled');
            return Command::SUCCESS;
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
                // Send the onboarding notification
                $user->notify(new AnnouncementNotification(
                    'Selesaikan onboarding dan dapatkan badge baru!',
                    'Selesaikan onboarding dan dapatkan badge baru! Klik notifikasi ini untuk melanjutkan checklist onboarding kamu',
                    '/onboarding',
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

                // Log the error
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
        $this->info("📊 Results:");
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

        return Command::SUCCESS;
    }
}
