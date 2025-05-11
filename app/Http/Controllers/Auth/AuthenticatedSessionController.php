<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        // Check if user email is verified after authentication
        if (!Auth::user()->hasVerifiedEmail() && !str_contains(session('auth_source', ''), 'google')) {
            // Log the user out since they haven't verified their email
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Check if this is an AJAX request (modal form uses AJAX)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'verification_required' => true,
                    'email' => $request->input('email'),
                    'message' => 'Please verify your email before logging in.'
                ], 403);
            }

            // For non-AJAX requests, redirect with message
            return redirect()->route('login')
                ->with('verification_required', true)
                ->with('email', $request->input('email'));
        }

        $request->session()->regenerate();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('home')
            ]);
        }

        return redirect()->intended(route('home', absolute: false));
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
