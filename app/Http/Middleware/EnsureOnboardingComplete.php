<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\OnboardingController;

class EnsureOnboardingComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to authenticated users
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $currentRoute = $request->route()->getName();

        // Routes that are always allowed regardless of onboarding status
        $exemptRoutes = [
            'logout',
            'account.destroy',
            'password.confirm',
            'verification.notice',
            'verification.send',
            'verification.verify'
        ];

        if (in_array($currentRoute, $exemptRoutes)) {
            return $next($request);
        }

        // Check if basic profile is completed
        $hasBasicProfile = $this->hasCompletedBasicProfile($user);

        // Handle onboarding routes specifically
        $onboardingRoutes = [
            'onboarding.welcome',
            'onboarding.checklist',
            'onboarding.basic-profile',
            'onboarding.update-basic-profile',
            'onboarding.badge-earned',
        ];

        if (in_array($currentRoute, $onboardingRoutes)) {
            // If basic profile is not completed, only allow basic-profile routes
            if (!$hasBasicProfile) {
                $allowedIncompleteRoutes = [
                    'onboarding.basic-profile',
                    'onboarding.update-basic-profile',
                ];

                if (!in_array($currentRoute, $allowedIncompleteRoutes)) {
                    // Redirect to basic profile if trying to access other onboarding routes
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'redirect' => route('onboarding.basic-profile'),
                            'message' => 'Silakan lengkapi profil dasar Anda terlebih dahulu.'
                        ], 302);
                    }

                    return redirect()->route('onboarding.basic-profile')
                        ->with('info', 'Silakan lengkapi profil dasar Anda terlebih dahulu.');
                }
            }

            // Allow access to onboarding routes
            return $next($request);
        }

        // For non-onboarding routes, check if user should complete onboarding
        if (OnboardingController::shouldShowOnboarding($user)) {
            // If this is an AJAX request, return JSON response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'redirect' => route('onboarding.basic-profile'),
                    'message' => 'Silakan lengkapi profil dasar Anda terlebih dahulu.'
                ], 302);
            }

            // For regular requests, redirect to basic profile
            return redirect()->route('onboarding.basic-profile')
                ->with('info', 'Silakan lengkapi profil dasar Anda terlebih dahulu.');
        }

        return $next($request);
    }

    /**
     * Check if user has completed basic profile
     * Basic profile requires: name AND job_title (both must be filled)
     */
    private function hasCompletedBasicProfile($user): bool
    {
        return !empty($user->name) && !empty($user->job_title);
    }
}
