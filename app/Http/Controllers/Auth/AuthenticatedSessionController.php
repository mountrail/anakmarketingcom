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
    public function create(): RedirectResponse
    {
        return redirect()->route('home')->with('show_auth_modal', 'login');
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

        // Check if user exists
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Tidak ada akun yang terdaftar dengan email ini. Silakan periksa email Anda atau daftar akun baru.'])
                ->with('show_auth_modal', 'login');
        }

        // Check if user has verified their email
        if (!$user->hasVerifiedEmail()) {
            return redirect()->back()
                ->with('verification_required', true)
                ->with('email', $request->email)
                ->with('error', 'Anda perlu memverifikasi alamat email Anda sebelum masuk.')
                ->with('show_auth_modal', 'login');
        }

        try {
            $request->authenticate();
            $request->session()->regenerate();

            return redirect()->intended(RouteServiceProvider::HOME);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // This catches wrong password attempts
            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['password' => 'Kata sandi yang Anda masukkan salah. Silakan coba lagi.'])
                ->with('show_auth_modal', 'login');
        } catch (\Exception $e) {
            // General fallback
            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Tidak dapat masuk. Silakan coba lagi.'])
                ->with('show_auth_modal', 'login');
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
