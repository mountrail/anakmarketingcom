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
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone_country_code' => ['required', 'string', 'max:5'],
                'phone_number' => ['required', 'string', 'max:20'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
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
