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
                'required',          // Changed from nullable to required
                'string',
                'min:8',            // Minimum 8 digits for phone numbers
                'max:12',           // Max 12 digits for phone numbers
                'regex:/^\d+$/',    // Only digits
            ],
            'phone_country_code' => [
                'required',          // Changed from nullable to required
                'string',
                'max:5',
                'regex:/^\d+$/',    // Only digits for country code
            ],
            'industry' => [
                'required',          // Changed from nullable to required
                Rule::in([
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
                ])
            ],

            'seniority' => [
                'required',          // Changed from nullable to required
                Rule::in([
                    'Junior Staff',
                    'Senior Staff',
                    'Assistant Manager',
                    'Manager',
                    'Vice President',
                    'Director (C-Level)',
                    'Owner',
                    'Others'
                ])
            ],

            'company_size' => [
                'required',          // Changed from nullable to required
                Rule::in([
                    '0-10',
                    '11-50',
                    '51-100',
                    '101-500',
                    '501++'
                ])
            ],

            'city' => [
                'required',          // Changed from nullable to required
                Rule::in([
                    'Bandung',
                    'Jabodetabek',
                    'Jogjakarta',
                    'Makassar',
                    'Medan',
                    'Surabaya',
                    'Others'
                ])
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Phone number validation messages
            'phone_number.required' => 'Nomor telepon harus diisi.',
            'phone_number.min' => 'Nomor telepon minimal 8 digit.',
            'phone_number.max' => 'Nomor telepon tidak boleh lebih dari 12 digit.',
            'phone_number.regex' => 'Nomor telepon hanya boleh berisi angka.',

            // Phone country code validation messages
            'phone_country_code.required' => 'Kode negara nomor telepon harus dipilih.',
            'phone_country_code.regex' => 'Kode negara hanya boleh berisi angka.',

            // Industry validation messages
            'industry.required' => 'Industri harus dipilih.',
            'industry.in' => 'Industri yang dipilih tidak valid.',

            // Seniority validation messages
            'seniority.required' => 'Senioritas harus dipilih.',
            'seniority.in' => 'Senioritas yang dipilih tidak valid.',

            // Company size validation messages
            'company_size.required' => 'Jumlah karyawan harus dipilih.',
            'company_size.in' => 'Jumlah karyawan yang dipilih tidak valid.',

            // City validation messages
            'city.required' => 'Kota harus dipilih.',
            'city.in' => 'Kota yang dipilih tidak valid.',
        ];
    }
}
