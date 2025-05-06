<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

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

            \Log::info('Google auth successful. User email: ' . $googleUser->email);

            // Check if user exists
            $user = User::where('email', $googleUser->email)->first();

            // If user doesn't exist, create a new one
            if (!$user) {
                \Log::info('Creating new user for Google auth');

                try {
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'provider' => 'google',
                        'provider_id' => $googleUser->id,
                        'email_verified_at' => now(),
                    ]);
                    \Log::info('New user created successfully: ' . $user->id);
                } catch (\Exception $createEx) {
                    \Log::error('Failed to create user: ' . $createEx->getMessage());
                    throw $createEx;
                }
            } else {
                \Log::info('Updating existing user for Google auth: ' . $user->id);

                // Update existing user logic...
            }

            // Log the user in
            Auth::login($user);
            \Log::info('User logged in successfully: ' . $user->id);

            return redirect()->intended(route('dashboard'));

        } catch (Exception $e) {
            \Log::error('Google sign in failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->route('login')
                ->with('error', 'Google sign in failed: ' . $e->getMessage());
        }
    }
}
