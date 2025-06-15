<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Ensure $errors is always available
        $errors = session('errors', new MessageBag());

        return view('auth.register', compact('errors'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Log all request data to see what's happening
        Log::info('Registration request data:', $request->except(['password', 'password_confirmation']));

        // Define validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone_country_code' => ['required', 'string', 'max:5', 'regex:/^\d+$/'],
            'phone_number' => [
                'required',
                'string',
                'min:8',        // Minimum 8 digits
                'max:12',       // Maximum 12 digits
                'regex:/^\d+$/' // Only digits
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Add validation rules for the select fields
            'industry' => ['required', 'string', 'in:Beauty,Consumer,Education,Financial or Banking,Health,Media,Products,Property,Services,Tech,Others'],
            'seniority' => ['required', 'string', 'in:Junior Staff,Senior Staff,Assistant Manager,Manager,Vice President,Director (C-Level),Owner,Others'],
            'company_size' => ['required', 'string', 'in:0-10,11-50,51-100,101-500,501++'],
            'city' => ['required', 'string', 'in:Bandung,Jabodetabek,Jogjakarta,Makassar,Medan,Surabaya,Others'],
        ];

        // Custom error messages
        $messages = [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone_country_code.required' => 'Kode negara wajib diisi.',
            'phone_country_code.regex' => 'Kode negara hanya boleh berisi angka.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'phone_number.min' => 'Nomor telepon minimal 8 digit.',
            'phone_number.max' => 'Nomor telepon maksimal 12 digit.',
            'phone_number.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'industry.required' => 'Industri wajib dipilih.',
            'industry.in' => 'Industri yang dipilih tidak valid.',
            'seniority.required' => 'Tingkat senioritas wajib dipilih.',
            'seniority.in' => 'Tingkat senioritas yang dipilih tidak valid.',
            'company_size.required' => 'Ukuran perusahaan wajib dipilih.',
            'company_size.in' => 'Ukuran perusahaan yang dipilih tidak valid.',
            'city.required' => 'Kota wajib dipilih.',
            'city.in' => 'Kota yang dipilih tidak valid.',
        ];

        // Validate request using Validator instead of validate() method
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return back()
                ->withErrors($validator)
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        // Combine phone country code and phone number
        $phone = '+' . $request->phone_country_code . ' ' . $request->phone_number;

        // Explicitly create the data array to ensure all fields are included
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
            // Add the new fields to the user data
            'industry' => $request->industry,
            'seniority' => $request->seniority,
            'company_size' => $request->company_size,
            'city' => $request->city,
        ];

        // Log the user data before creation (password will be hashed)
        Log::info('User creation data:', $userData);

        try {
            // Try creating user with the data array
            $user = User::create($userData);

            Log::info('User created successfully with ID: ' . $user->id);

            event(new Registered($user));

            // Send pinned onboarding notification to the new user
            $user->notify(new AnnouncementNotification(
                'Selesaikan onboarding dan dapatkan badge baru!',
                'Selesaikan onboarding dan dapatkan badge baru! Klik notifikasi ini untuk melanjutkan checklist onboarding kamu',
                '/onboarding/checklist',
                true // isPinned = true
            ));

            Log::info('Onboarding notification sent to user ID: ' . $user->id);

            // IMPORTANT CHANGE: Don't log the user in automatically
            // Auth::login($user); - This line is removed

            // Handle JSON response if requested
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('verification.notice')
                ]);
            }

            // Redirect to verification notice page
            return redirect()->route('verification.notice');
        } catch (\Exception $e) {
            // Log any exceptions that occur
            Log::error('Error creating user: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Return JSON error if requested
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['error' => ['Pendaftaran gagal. Silakan coba lagi.']]
                ], 500);
            }

            // Create a MessageBag for errors
            $errors = new MessageBag(['error' => 'Pendaftaran gagal. Silakan coba lagi.']);

            // Return back with an error message instead of rethrowing the exception
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors($errors);
        }
    }
}
