<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('account.edit', [
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

        return Redirect::route('account.edit')->with('status', 'profile-updated');
    }

    /**
     * Display the specified user's profile.
     */
    public function show(User $user)
    {
        $isOwner = auth()->check() && auth()->id() === $user->id;

        // Get user's posts with pagination
        $posts = $user->posts()
            ->withCount('answers')
            ->latest()
            ->paginate(10);

        // Get editor's picks for sidebar
        $editorPicks = Post::featured()
            ->where('featured_type', '!=', 'none')
            ->with(['user', 'answers'])
            ->latest()
            ->take(5)
            ->get();

        // Get followers - get the user IDs first, then get the User models
        $followerIds = $user->followers()->pluck('user_id');
        $followers = User::whereIn('id', $followerIds)
            ->select('id', 'name', 'profile_picture', 'avatar', 'job_title', 'company')
            ->latest()
            ->limit(50)
            ->get();

        // Get following - get the user IDs first, then get the User models
        $followingIds = $user->followings()->pluck('followable_id');
        $following = User::whereIn('id', $followingIds)
            ->select('id', 'name', 'profile_picture', 'avatar', 'job_title', 'company')
            ->latest()
            ->limit(50)
            ->get();

        // Get counts for display
        $followersCount = $user->followers()->count();
        $followingCount = $user->followings()->count();

        // Share editorPicks for the sidebar
        view()->share('editorPicks', $editorPicks);

        return view('profile.show', compact(
            'user',
            'isOwner',
            'posts',
            'followers',
            'following',
            'followersCount',
            'followingCount'
        ));
    }

    /**
     * Update the user's basic profile information (name, job, company, profile picture).
     */
    public function updateBasicInfo(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = auth()->user();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::delete('public/' . $user->profile_picture);
            }

            // Store new profile picture
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/profile-pictures', $filename, 'public');

            $user->profile_picture = $path;
        }

        // Update basic profile information
        $user->update([
            'name' => $request->name,
            'job_title' => $request->job_title,
            'company' => $request->company,
            'profile_picture' => $user->profile_picture,
        ]);

        return redirect()->route('profile.show', $user)->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update the user's bio/description.
     */
    public function updateBio(Request $request): RedirectResponse
    {
        $request->validate([
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = auth()->user();

        // Update bio
        $user->update([
            'bio' => $request->bio,
        ]);

        return redirect()->route('profile.show', $user)->with('success', 'Deskripsi berhasil diperbarui!');
    }

    /**
     * Update the user's profile information from public profile page.
     * @deprecated Use updateBasicInfo() and updateBio() instead
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = auth()->user();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::delete('public/' . $user->profile_picture);
            }

            // Store new profile picture
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/profile-pictures', $filename, 'public');

            $user->profile_picture = $path;
        }

        $user->update([
            'name' => $request->name,
            'bio' => $request->bio,
            'job_title' => $request->job_title,
            'company' => $request->company,
            'profile_picture' => $user->profile_picture,
        ]);

        return redirect()->route('profile.show', $user)->with('success', 'Profile updated successfully!');
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

        // Delete profile picture if exists
        if ($user->profile_picture) {
            Storage::delete('public/' . $user->profile_picture);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
