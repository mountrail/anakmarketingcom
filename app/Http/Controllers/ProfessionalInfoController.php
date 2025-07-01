<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfessionalInfoController extends Controller
{
    public function form()
    {
        $user = auth()->user();

        // If user already has professional info, redirect to onboarding or home
        if ($user->hasProfessionalInfo()) {
            // Check if they need onboarding
            if (\App\Http\Controllers\OnboardingController::shouldShowOnboarding($user)) {
                return redirect()->route('onboarding.welcome');
            }
            return redirect()->route('home');
        }

        return view('auth.professional-info', compact('user'));
    }

    public function store(Request $request)
    {
        \Log::info('Professional info store method called', [
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);
        $rules = [
            'industry' => ['required', 'string', 'in:Beauty,Consumer,Education,Financial or Banking,Health,Media,Products,Property,Services,Tech,Others'],
            'seniority' => ['required', 'string', 'in:Junior Staff,Senior Staff,Assistant Manager,Manager,Vice President,Director (C-Level),Owner,Others'],
            'company_size' => ['required', 'string', 'in:0-10,11-50,51-100,101-500,501++'],
            'city' => ['required', 'string', 'in:Bandung,Jabodetabek,Jogjakarta,Makassar,Medan,Surabaya,Others'],
        ];

        $messages = [
            'industry.required' => 'Industri wajib dipilih.',
            'industry.in' => 'Industri yang dipilih tidak valid.',
            'seniority.required' => 'Tingkat senioritas wajib dipilih.',
            'seniority.in' => 'Tingkat senioritas yang dipilih tidak valid.',
            'company_size.required' => 'Ukuran perusahaan wajib dipilih.',
            'company_size.in' => 'Ukuran perusahaan yang dipilih tidak valid.',
            'city.required' => 'Kota wajib dipilih.',
            'city.in' => 'Kota yang dipilih tidak valid.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $user = auth()->user();
            $user->update([
                'industry' => $request->industry,
                'seniority' => $request->seniority,
                'company_size' => $request->company_size,
                'city' => $request->city,
            ]);

            Log::info('Professional info updated for user ID: ' . $user->id);

            // After saving, check if they need onboarding
            if (\App\Http\Controllers\OnboardingController::shouldShowOnboarding($user)) {
                return redirect()->route('onboarding.welcome')
                    ->with('success', 'Informasi profesional berhasil disimpan!');
            }

            return redirect()->route('home')
                ->with('success', 'Informasi profesional berhasil disimpan!');

        } catch (\Exception $e) {
            Log::error('Error updating professional info: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.');
        }
    }
}
