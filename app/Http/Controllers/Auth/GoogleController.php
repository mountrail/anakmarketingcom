<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
                        'email_verified_at' => now(),
                    ];

                    \Log::info('User data for creation:', ['data' => array_keys($userData)]);

                    $user = User::create($userData);

                    \Log::info('New user created successfully with ID: ' . $user->id);
                } catch (\Exception $createEx) {
                    \Log::error('Failed to create user: ' . $createEx->getMessage());
                    \Log::error($createEx->getTraceAsString());

                    // Rethrow to be caught by outer catch block
                    throw $createEx;
                }
            } else {
                \Log::info('Existing user found: ' . $user->id);

                // Update existing user with Google information
                // This ensures proper linkage with Google and verification status
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'provider' => 'google',
                    'provider_id' => $googleUser->id,
                    'email_verified_at' => $user->email_verified_at ?? now(), // Set verification timestamp if not already set
                ]);

                \Log::info('Existing user updated with Google information');
            }

            // Log the user in
            Auth::login($user);
            \Log::info('User logged in successfully: ' . $user->id);

            return redirect()->intended(route('home'));

        } catch (\Exception $e) {
            \Log::error('Google sign in exception: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return redirect()->route('login')
                ->withErrors(['email' => 'Google sign in failed: ' . $e->getMessage()]);
        }
    }
}
