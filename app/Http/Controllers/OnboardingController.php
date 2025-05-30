<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\BadgeService;
use App\Models\Badge;

class OnboardingController extends Controller
{
    /**
     * Display the welcome page
     */
    public function welcome(): View
    {
        return view('onboarding.welcome', [
            'showSidebar' => false,
            'user' => auth()->user()
        ]);
    }

    /**
     * Display the checklist page
     */
    public function checklist(): View
    {
        // Get current user's onboarding status
        $user = auth()->user();
        $onboardingStatus = $this->getOnboardingStatus($user);

        return view('onboarding.checklist', [
            'showSidebar' => false,
            'onboardingStatus' => $onboardingStatus,
            'user' => $user
        ]);
    }

    /**
     * Show the basic profile step
     */
    public function basicProfile(): View
    {
        return view('onboarding.basic-profile', [
            'showSidebar' => false,
            'user' => auth()->user()
        ]);
    }

    /**
     * Display the badge earned page
     */
    public function badgeEarned(Request $request): View
    {
        $user = auth()->user();
        $badgeName = $request->query('badge', 'Perkenalkan Saya');

        // Get the specified badge
        $badge = Badge::where('name', $badgeName)->first();

        // If badge doesn't exist or user doesn't have it, redirect to checklist
        if (!$badge || !$user->hasBadge($badgeName)) {
            return redirect()->route('onboarding.checklist');
        }

        return view('onboarding.badge-earned', [
            'showSidebar' => false,
            'badge' => $badge,
            'user' => $user
        ]);
    }

    /**
     * Claim the "Marketers Onboard!" badge when all missions are completed
     */
    public function claimBadge(): RedirectResponse
    {
        try {
            $user = auth()->user();

            // Check and award the "Marketers Onboard!" badge
            $badgeAwarded = BadgeService::checkMarketersOnboard($user);

            if ($badgeAwarded) {
                // Redirect to badge-earned page with the specific badge
                return redirect()->route('onboarding.badge-earned', ['badge' => 'Marketers Onboard!'])
                    ->with('success', 'Selamat! Kamu berhasil mendapatkan badge "Marketers Onboard!"');
            } else {
                // If badge wasn't awarded (already has it or doesn't meet requirements)
                return redirect()->route('onboarding.checklist')
                    ->with('info', 'Pastikan semua misi onboarding sudah diselesaikan.');
            }

        } catch (\Exception $e) {
            Log::error('Error claiming Marketers Onboard badge for user ' . auth()->id() . ': ' . $e->getMessage());

            return redirect()->route('onboarding.checklist')
                ->with('error', 'Terjadi kesalahan saat mengklaim badge. Silakan coba lagi.');
        }
    }

    /**
     * Update basic profile information during onboarding
     */
    public function updateBasicProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'profile_picture' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:5120', // 5MB in kilobytes
            ],
        ], [
            'name.required' => 'Nama harus diisi',
            'profile_picture.image' => 'File harus berupa gambar',
            'profile_picture.mimes' => 'Format file harus JPG atau PNG',
            'profile_picture.max' => 'Foto lebih dari 5 MB',
        ]);

        try {
            $user = auth()->user();

            // Update basic profile information
            $user->update([
                'name' => $request->name,
                'job_title' => $request->job_title,
                'company' => $request->company,
            ]);

            // Handle profile picture upload if provided
            if ($request->hasFile('profile_picture')) {
                // Remove old profile picture if exists
                $user->clearMediaCollection('profile_pictures');

                // Add new profile picture
                $user->addMediaFromRequest('profile_picture')
                    ->toMediaCollection('profile_pictures');
            }

            // Mark basic profile as completed
            $this->markOnboardingStepCompleted($user, 'basic_profile');

            // Check and award "Perkenalkan Saya" badge
            $badgeAwarded = BadgeService::checkPerkenalkanSaya($user);

            // If badge was just awarded, redirect to badge-earned page
            if ($badgeAwarded) {
                return redirect()->route('onboarding.badge-earned', ['badge' => 'Perkenalkan Saya'])
                    ->with('success', 'Profil dasar berhasil disimpan!');
            }

            // Otherwise, redirect to checklist
            return redirect()->route('onboarding.checklist')
                ->with('success', 'Profil dasar berhasil disimpan!');

        } catch (\Exception $e) {
            Log::error('Onboarding basic profile update failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.']);
        }
    }

    /**
     * Get user's onboarding status
     */
    private function getOnboardingStatus($user): array
    {
        // Check if user has completed basic profile
        // User must have: name (required for registration) + job_title
        // Profile picture and company are optional
        $hasBasicProfile = !empty($user->name) && !empty($user->job_title);

        // Check if user has accessed notifications center
        $hasAccessedNotifications = $this->hasAccessedNotificationCenter($user);

        // Check other onboarding steps
        $hasFirstPost = $user->posts()->count() > 0;
        $hasFirstAnswer = $user->answers()->count() > 0;
        $hasFollowedUser = $user->followings()->count() > 0;

        return [
            'basic_profile' => $hasBasicProfile,
            'accessed_notifications' => $hasAccessedNotifications,
            'first_answer' => $hasFirstAnswer,
            'first_post' => $hasFirstPost,
            'followed_user' => $hasFollowedUser,
        ];
    }

    /**
     * Check if user has accessed notification center
     */
    private function hasAccessedNotificationCenter($user): bool
    {
        // Check if user has any notifications that have been marked as read
        // This indicates they've accessed the notification center
        if ($user->notifications()->whereNotNull('read_at')->exists()) {
            return true;
        }

        // Alternative: Check if user has the onboarding step marked as completed
        // You might want to add a tracking mechanism for this
        return $user->onboarding_steps &&
            in_array('accessed_notifications', json_decode($user->onboarding_steps, true) ?? []);
    }

    /**
     * Mark notification center as accessed (call this from NotificationController)
     */
    public static function markNotificationCenterAccessed($user): void
    {
        $steps = json_decode($user->onboarding_steps, true) ?? [];

        if (!in_array('accessed_notifications', $steps)) {
            $steps[] = 'accessed_notifications';
            $user->update(['onboarding_steps' => json_encode($steps)]);

            Log::info("Notification center accessed for user {$user->id}");
        }
    }

    /**
     * Mark an onboarding step as completed
     */
    private function markOnboardingStepCompleted($user, $step): void
    {
        $steps = json_decode($user->onboarding_steps, true) ?? [];

        if (!in_array($step, $steps)) {
            $steps[] = $step;
            $user->update(['onboarding_steps' => json_encode($steps)]);
        }

        Log::info("Onboarding step '$step' completed for user {$user->id}");
    }

    /**
     * Check if user should see onboarding (first-time login)
     */
    public static function shouldShowOnboarding($user): bool
    {
        // Show onboarding if user hasn't completed basic profile
        // Basic profile requires both name AND job_title to be filled
        return empty($user->name) || empty($user->job_title);
    }
}
