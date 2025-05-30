<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view. Deprecated in favor of the new auth modal.
     * This method is kept for backward compatibility and will be removed in future versions.
     * Instead, use the auth modal for login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Make sure we have validated the request
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if user has verified their email
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && !$user->hasVerifiedEmail()) {
            // Add show_auth_modal=login to the redirect to trigger modal opening
            return redirect()->back()
                ->with('verification_required', true)
                ->with('email', $request->email)
                ->with('error', 'You need to verify your email address before logging in.')
                ->with('show_auth_modal', 'login'); // Add this line to trigger modal
        }

        try {
            $request->authenticate();
            $request->session()->regenerate();

            return redirect()->intended(RouteServiceProvider::HOME);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->with('show_auth_modal', 'login'); // Also add here for regular login failures
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
