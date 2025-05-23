<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
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
            'phone_number.min' => 'Phone number must be at least 8 digits long.',
            'phone_number.max' => 'Phone number cannot exceed 12 digits.',
            'phone_number.regex' => 'Phone number must contain only digits.',
            'phone_country_code.regex' => 'Country code must contain only digits.',
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
                    'errors' => ['error' => ['Registration failed. Please try again.']]
                ], 500);
            }

            // Return back with an error message instead of rethrowing the exception
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }
}
