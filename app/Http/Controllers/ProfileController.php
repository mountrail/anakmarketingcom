<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Log the incoming request data for debugging
        \Log::info('Profile update request data:', $request->all());

        // Get validated data
        $validatedData = $request->validated();

        // Remove phone fields from validated data since we'll handle them separately
        unset($validatedData['phone_country_code']);
        unset($validatedData['phone_number']);

        // Fill user with validated data
        $request->user()->fill($validatedData);

        // Handle phone number concatenation
        if ($request->filled('phone_country_code') && $request->filled('phone_number')) {
            $countryCode = preg_replace('/[^0-9]/', '', $request->phone_country_code);
            $phoneNumber = preg_replace('/[^0-9]/', '', $request->phone_number);

            // Store with a space between country code and number for easier parsing
            $request->user()->phone = '+' . $countryCode . ' ' . $phoneNumber;

            // Log the processed phone number
            \Log::info('Setting phone to: ' . $request->user()->phone);
        } else if (!$request->filled('phone_country_code') && !$request->filled('phone_number')) {
            // If both fields are empty, set phone to null
            $request->user()->phone = null;
        } else {
            // If only one field is filled, keep the existing value
            \Log::info('Only one phone field filled, keeping existing value: ' . $request->user()->phone);
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
