<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');

Route::post('reset-password', [NewPasswordController::class, 'store'])
    ->name('password.store');

Route::middleware('guest')->group(function () {
    Route::get('/register', function () {
        return redirect()->route('home')->with('show_auth_modal', 'register');
    })->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('/login', function () {
        return redirect()->route('home')->with('show_auth_modal', 'login');
    })->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');



    // Registration step 1 validation
    Route::post('/register/validate-step1', [App\Http\Controllers\Auth\RegisterController::class, 'validateStep1'])
        ->middleware('guest')
        ->name('register.validate.step1');

    // Updated guest-accessible version of verification resend with AJAX support
    Route::post('resend-verification', function (Illuminate\Http\Request $request) {
        // Validate the request
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verification link has been sent!'
                ]);
            }

            return back()->with('status', 'Verification link has been sent!');
        }

        // Check if it's an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to resend verification email.'
            ], 400);
        }

        return back()->with('error', 'Unable to resend verification email.');
    })->middleware(['throttle:6,1', 'web'])->name('verification.guest.send');

    // Move the email verification route to be accessible by guests
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['throttle:6,1'])
        ->name('verification.verify');
});

Route::middleware('auth')->group(function () {

    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // The verify-email/{id}/{hash} route has been moved to the guest middleware group

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Authenticated user password reset route (for account page)
    Route::post('password-reset-authenticated', [PasswordResetLinkController::class, 'storeAuthenticated'])
        ->name('password.email.authenticated');
});
