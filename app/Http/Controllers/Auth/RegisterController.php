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
