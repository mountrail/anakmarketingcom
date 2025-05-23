<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Validate the first step of registration
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateStep1(Request $request): JsonResponse
    {
        try {
            // Log validation attempt for debugging
            \Illuminate\Support\Facades\Log::info('Step 1 validation attempt', [
                'email' => $request->email,
                'has_password' => !empty($request->password)
            ]);

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone_country_code' => ['required', 'string', 'max:5', 'regex:/^\d+$/'],
                'phone_number' => [
                    'required',
                    'string',
                    'min:8',        // Minimum 8 digits
                    'max:12',       // Maximum 12 digits
                    'regex:/^\d+$/' // Only digits
                ],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ], [
                'phone_number.min' => 'Phone number must be at least 8 digits long.',
                'phone_number.max' => 'Phone number cannot exceed 12 digits.',
                'phone_number.regex' => 'Phone number must contain only digits.',
                'phone_country_code.regex' => 'Country code must contain only digits.',
            ]);

            if ($validator->fails()) {
                // Log validation failures for debugging
                \Illuminate\Support\Facades\Log::info('Step 1 validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exception in validateStep1: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Handle AJAX responses to the RegisteredUserController store method
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function ajaxRegistrationSuccess(Request $request, User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'redirect' => route('verification.notice')
        ]);
    }
}
