<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

            // Redirect to checklist with basic profile completed
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
        // User must have: name (required for registration) + (job_title OR company) + profile_picture
        $hasBasicProfile = !empty($user->name) &&
            (!empty($user->job_title) || !empty($user->company)) &&
            $user->hasProfilePicture();

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
        // This means: missing job_title AND company AND profile_picture
        return empty($user->job_title) &&
            empty($user->company) &&
            !$user->hasProfilePicture();
    }
}
