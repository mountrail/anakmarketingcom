<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\Post;
use App\Services\BadgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\OnboardingController;

class ProfileController extends Controller
{
    /**
     * Check if authenticated user needs onboarding and redirect if necessary
     */
    private function checkOnboardingRequired()
    {
        if (auth()->check()) {
            // First check professional info
            if (!auth()->user()->hasProfessionalInfo()) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'redirect' => route('professional-info.form'),
                        'message' => 'Silakan lengkapi informasi profesional Anda terlebih dahulu.'
                    ], 302);
                }

                return redirect()->route('professional-info.form')
                    ->with('info', 'Silakan lengkapi informasi profesional Anda terlebih dahulu.');
            }

            // Then check onboarding
            if (OnboardingController::shouldShowOnboarding(auth()->user())) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'redirect' => route('onboarding.basic-profile'),
                        'message' => 'Silakan lengkapi profil dasar Anda terlebih dahulu.'
                    ], 302);
                }

                return redirect()->route('onboarding.basic-profile')
                    ->with('info', 'Silakan lengkapi profil dasar Anda terlebih dahulu.');
            }
        }
        return null;
    }

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
     * Display the profile edit page with all editable sections.
     */
    public function editProfile(Request $request): View
    {
        // Check onboarding for authenticated users
        $onboardingCheck = $this->checkOnboardingRequired();
        if ($onboardingCheck) {
            return $onboardingCheck;
        }

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

        // Additional validation for required fields
        $request->validate([
            'phone_country_code' => 'required',
            'phone_number' => 'required|string|min:8',
            'industry' => 'required|string',
            'seniority' => 'required|string',
            'company_size' => 'required|string',
            'city' => 'required|string',
        ], [
            'phone_country_code.required' => 'Kode negara nomor telepon harus dipilih.',
            'phone_number.required' => 'Nomor telepon harus diisi.',
            'phone_number.min' => 'Nomor telepon minimal 8 digit.',
            'industry.required' => 'Industri harus dipilih.',
            'seniority.required' => 'Senioritas harus dipilih.',
            'company_size.required' => 'Jumlah karyawan harus dipilih.',
            'city.required' => 'Kota harus dipilih.',
        ]);

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
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        try {
            $request->user()->save();

            return Redirect::route('account.edit')->with('success', 'Informasi akun berhasil diperbarui!');
        } catch (\Exception $e) {
            \Log::error('Account update failed: ' . $e->getMessage());

            return Redirect::route('account.edit')->with('error', 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified user's profile.
     */
    public function show($userIdentifier)
    {
        // Check onboarding for authenticated users
        $onboardingCheck = $this->checkOnboardingRequired();
        if ($onboardingCheck) {
            return $onboardingCheck;
        }

        // Try to find the user by ID or any other identifier you're using
        $user = null;

        // If it's numeric, try to find by ID
        if (is_numeric($userIdentifier)) {
            $user = User::find($userIdentifier);
        } else {
            // If it's not numeric, you might be using username, slug, or some other identifier
            // Adjust this based on your actual user identification method
            $user = User::where('id', $userIdentifier)
                ->orWhere('name', $userIdentifier)
                ->first();
        }

        // If user not found, show custom 404 page
        if (!$user) {
            // Return custom 404 view for user profiles
            return response()->view('errors.user-not-found', [], 404);
        }

        $isOwner = auth()->check() && auth()->id() === $user->id;

        // Get user's posts with pagination
        $posts = $user->posts()
            ->withCount('answers')
            ->latest()
            ->paginate(10);

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
     * Update basic profile information (Name, Job Title, Company).
     */
    public function updateBasicInfo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
        ]);

        try {
            $user = auth()->user();

            // Update basic profile information only
            $user->update([
                'name' => $request->name,
                'job_title' => $request->job_title,
                'company' => $request->company,
            ]);

            return redirect()->route('profile.edit-profile')
                ->with('success', 'Informasi dasar berhasil diperbarui!');

        } catch (\Exception $e) {
            \Log::error('Profile basic info update failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.');
        }
    }

    /**
     * Update profile bio/description.
     */
    public function updateBio(Request $request)
    {
        $request->validate([
            'bio' => ['nullable', 'string', 'max:1625'],
        ], [
            'bio.max' => 'Deskripsi tidak boleh lebih dari 1625 karakter.',
        ]);

        // Add word count and character count validation
        if ($request->bio) {
            $wordCount = str_word_count(strip_tags($request->bio));
            $charCount = strlen($request->bio);

            if ($wordCount > 250) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['bio' => 'Deskripsi tidak boleh lebih dari 250 kata.']);
            }

            if ($charCount > 1625) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['bio' => 'Deskripsi tidak boleh lebih dari 1625 karakter.']);
            }
        }

        try {
            $user = auth()->user();

            // Update bio
            $user->update([
                'bio' => $request->bio,
            ]);

            return redirect()->route('profile.edit-profile')
                ->with('success', 'Deskripsi berhasil diperbarui!');

        } catch (\Exception $e) {
            \Log::error('Profile bio update failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.');
        }
    }

    /**
     * Update the user's profile picture.
     * This is the dedicated method for handling profile picture uploads
     */
    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:5120', // 5MB in kilobytes
            ],
        ], [
            'profile_picture.required' => 'Foto profil harus dipilih',
            'profile_picture.image' => 'File harus berupa gambar',
            'profile_picture.mimes' => 'Format file harus JPG atau PNG',
            'profile_picture.max' => 'Foto lebih dari 5 MB',
        ]);

        try {
            $user = Auth::user();

            // Remove old profile picture if exists
            $user->clearMediaCollection('profile_pictures');

            // Add new profile picture
            $mediaItem = $user->addMediaFromRequest('profile_picture')
                ->toMediaCollection('profile_pictures');

            // Always redirect to edit page after profile picture update
            return redirect()->route('profile.edit-profile')
                ->with('success', 'Foto berhasil diubah!');

        } catch (\Exception $e) {
            \Log::error('Profile picture upload error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupload foto');
        }
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

    /**
     * Update the user's displayed badges.
     */
    public function updateBadges(Request $request): RedirectResponse
    {
        $request->validate([
            'badges' => 'nullable|array|max:3',
            'badges.*' => 'exists:badges,id'
        ]);

        try {
            $badgeIds = $request->input('badges', []);
            BadgeService::updateDisplayedBadges(auth()->user(), $badgeIds);

            return redirect()->route('profile.edit-profile')
                ->with('success', 'Badge berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating badges: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui badge.');
        }
    }
}
