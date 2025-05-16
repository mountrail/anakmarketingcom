<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class CustomEmailVerificationRequest extends FormRequest
{
    /**
     * The user attempting verification.
     *
     * @var \App\Models\User|null
     */
    protected $verificationUser;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Find the user by ID from the route parameter
        $this->verificationUser = User::find($this->route('id'));

        if (!$this->verificationUser) {
            return false;
        }

        // Check if the signature is valid
        if (!URL::hasValidSignature($this)) {
            return false;
        }

        // Check if the hash is valid for this user's email
        $hash = sha1($this->verificationUser->getEmailForVerification());
        if (!hash_equals($hash, (string) $this->route('hash'))) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfill(): void
    {
        if (!$this->verificationUser->hasVerifiedEmail()) {
            $this->verificationUser->markEmailAsVerified();

            event(new Verified($this->verificationUser));
        }
    }

    /**
     * Override the default user() method to return our verification user
     * instead of relying on the authenticated user.
     *
     * @param string|null $guard
     * @return \App\Models\User
     */
    public function user($guard = null)
    {
        return $this->verificationUser;
    }
}
