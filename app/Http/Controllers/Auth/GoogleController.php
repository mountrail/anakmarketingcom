<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OnboardingController;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            \Log::info('Google auth successful for: ' . $googleUser->email);

            // Check if user exists
            $user = User::where('email', $googleUser->email)->first();

            $isNewUser = false;
            $needsOnboarding = OnboardingController::shouldShowOnboarding($user);

            // If user doesn't exist, create a new one
            if (!$user) {
                \Log::info('Creating new user from Google auth: ' . $googleUser->email);

                try {
                    $userData = [
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'provider' => 'google',
                        'provider_id' => $googleUser->id,
                        'password' => bcrypt(Str::random(24)), // Random password
                        'email_verified_at' => now(), // Auto-verify Google users
                    ];

                    \Log::info('User data for creation:', ['data' => array_keys($userData)]);

                    $user = User::create($userData);
                    $isNewUser = true;

                    $user->assignRole('user');

                    $needsOnboarding = true; // New users always need onboarding
                    $needsOnboarding = true; // New users always need onboarding

                    \Log::info('New user created successfully with ID: ' . $user->id . ' and auto-verified');
                } catch (\Exception $createEx) {
                    \Log::error('Failed to create user: ' . $createEx->getMessage());
                    \Log::error($createEx->getTraceAsString());

                    // Rethrow to be caught by outer catch block
                    throw $createEx;
                }
            } else {
                \Log::info('Existing user found: ' . $user->id);

                // Check if existing user needs to complete onboarding
                $needsOnboarding = OnboardingController::shouldShowOnboarding($user);
                // Check if existing user needs to complete onboarding
                $needsOnboarding = OnboardingController::shouldShowOnboarding($user);

                // Update existing user with Google information and auto-verify
                $updateData = [
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'provider' => 'google',
                    'provider_id' => $googleUser->id,
                ];

                // Auto-verify if not already verified
                if (!$user->hasVerifiedEmail()) {
                    $updateData['email_verified_at'] = now();
                    \Log::info('Auto-verifying existing user: ' . $user->id);
                }

                $user->update($updateData);

                \Log::info('Existing user updated with Google information');
            }

            // Send pinned onboarding notification for new users
            if ($isNewUser) {
                $user->notify(new AnnouncementNotification(
                    'Selesaikan onboarding dan dapatkan badge baru!',
                    'Selesaikan onboarding dan dapatkan badge baru! Klik notifikasi ini untuk melanjutkan checklist onboarding kamu',
                    '/onboarding/checklist',
                    true // isPinned = true
                ));

                \Log::info('Onboarding notification sent to new Google user ID: ' . $user->id);
            }

            // Log the user in
            // Log the user in
            Auth::login($user);
            \Log::info('User logged in successfully: ' . $user->id);

            // Check professional info first - HIGHEST PRIORITY
            if (!$user->hasProfessionalInfo()) {
                return redirect()->route('professional-info.form')
                    ->with('info', 'Silakan lengkapi informasi profesional Anda terlebih dahulu.');
            }

            // Then check onboarding status
            if ($needsOnboarding) {
                // For new users, redirect to welcome page (consistent with email verification flow)
                if ($isNewUser) {
                    return redirect()->route('onboarding.welcome')
                        ->with('info', 'Selamat datang! Mari mulai dengan onboarding process.');
                }

                // For existing users who haven't completed onboarding, go directly to basic profile
                return redirect()->route('onboarding.basic-profile')
                    ->with('info', 'Silakan lengkapi profil dasar Anda terlebih dahulu.');
            }

            return redirect()->intended(route('home'));

        } catch (\Exception $e) {
            \Log::error('Google sign in exception: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return redirect()->route('login')
                ->withErrors(['email' => 'Google sign in failed: ' . $e->getMessage()]);
        }
    }
}
