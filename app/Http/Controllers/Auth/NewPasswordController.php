<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        // Check if user is authenticated and the token is for their email
        if (auth()->check()) {
            $user = auth()->user();

            // Verify the token is valid for this user's email
            $status = Password::getRepository()->exists($user, $request->route('token'));

            if ($status) {
                // Token is valid, show reset form for authenticated user
                return view('auth.reset-password-authenticated', ['request' => $request]);
            } else {
                // Invalid token, redirect to account page with error
                return redirect()->route('account.edit')
                    ->withErrors(['password_reset' => 'Token reset password tidak valid atau sudah kedaluwarsa.']);
            }
        }

        // Guest user - show normal reset form
        return view('auth.reset-password', ['request' => $request]);
    }
    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Check if user is authenticated to determine redirect
        if (auth()->check()) {
            // Authenticated user - redirect to account page
            return $status == Password::PASSWORD_RESET
                ? redirect()->route('account.edit')->with('success', 'Password berhasil direset!')
                : back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
        } else {
            // Guest user - redirect to login
            return $status == Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', __($status))
                : back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
        }
    }
}
