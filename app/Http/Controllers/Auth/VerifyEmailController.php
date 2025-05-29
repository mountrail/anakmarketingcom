<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OnboardingController;
use Illuminate\Auth\Events\Verified;
use App\Http\Requests\Auth\CustomEmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(CustomEmailVerificationRequest $request): RedirectResponse
    {
        // Verification logic is handled in the custom request's authorize method

        // If already verified, just redirect
        if ($request->user()->hasVerifiedEmail()) {
            // User might not be logged in, so log them in
            if (!Auth::check()) {
                Auth::login($request->user());
            }

            // Check if user should see onboarding (first-time login)
            if (OnboardingController::shouldShowOnboarding($request->user())) {
                return redirect()->route('onboarding.welcome');
            }

            return redirect()->intended(route('home', absolute: false) . '?verified=1');
        }

        // Otherwise fulfill the verification
        $request->fulfill();

        // Log the user in after verification
        Auth::login($request->user());

        // This is their first-time login, so redirect to onboarding
        return redirect()->route('onboarding.welcome');
    }
}
