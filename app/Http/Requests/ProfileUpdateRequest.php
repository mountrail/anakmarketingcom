<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:12',           // Max 12 digits for phone numbers (3 for country codes, 15 total in international standard)
                'regex:/^\d+$/',    // Only digits
                function ($attribute, $value, $fail) {
                    if (!empty($value) && empty($this->phone_country_code)) {
                        $fail('Phone number requires a country code.');
                    }
                }
            ],
            'industry' => ['nullable', Rule::in([
                'Beauty',
                'Consumer',
                'Education',
                'Financial or Banking',
                'Health',
                'Media',
                'Products',
                'Property',
                'Services',
                'Tech',
                'Others'
            ])],

            'seniority' => ['nullable', Rule::in([
                'Junior Staff',
                'Senior Staff',
                'Assistant Manager',
                'Manager',
                'Vice President',
                'Director (C-Level)',
                'Owner',
                'Others'
            ])],

            'company_size' => ['nullable', Rule::in([
                '0-10',
                '11-50',
                '51-100',
                '101-500',
                '501++'
            ])],

            'city' => ['nullable', Rule::in([
                'Bandung',
                'Jabodetabek',
                'Jogjakarta',
                'Makassar',
                'Medan',
                'Surabaya',
                'Others'
            ])],
        ];
    }
}
