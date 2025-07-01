@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="mt-24 px-6 max-w-md mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-branding-primary dark:text-white mb-2">
                One Last Step
            </h1>
        </div>

        <form method="POST" action="{{ route('professional-info.store') }}">
            @csrf

            <!-- Display any general errors -->
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Industry Field --}}
            <div class="mb-4">
                <x-input-label for="industry" :value="__('Industry')" />
                <x-select-input id="industry" name="industry" :options="[
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
                    'Others',
                ]" :selected="old('industry', $user->industry)" :placeholder="__('--Select Industry--')"
                    class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('industry')" />
            </div>

            {{-- Seniority Field --}}
            <div class="mb-4">
                <x-input-label for="seniority" :value="__('Seniority')" />
                <x-select-input id="seniority" name="seniority" :options="[
                    'Junior Staff',
                    'Senior Staff',
                    'Assistant Manager',
                    'Manager',
                    'Vice President',
                    'Director (C-Level)',
                    'Owner',
                    'Others',
                ]" :selected="old('seniority', $user->seniority)" :placeholder="__('--Select Seniority--')"
                    class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('seniority')" />
            </div>

            {{-- Company Size Field --}}
            <div class="mb-4">
                <x-input-label for="company_size" :value="__('Company Size')" />
                <x-select-input id="company_size" name="company_size" :options="['0-10', '11-50', '51-100', '101-500', '501++']" :selected="old('company_size', $user->company_size)"
                    :placeholder="__('--Select Company Size--')" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('company_size')" />
            </div>

            {{-- City Field --}}
            <div class="mb-6">
                <x-input-label for="city" :value="__('City')" />
                <x-select-input id="city" name="city" :options="['Bandung', 'Jabodetabek', 'Jogjakarta', 'Makassar', 'Medan', 'Surabaya', 'Others']" :selected="old('city', $user->city)" :placeholder="__('--Select City--')"
                    class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>

            <div class="flex justify-end">
                <x-primary-button class="w-full justify-center" id="professional-info-submit"
                    onclick="this.disabled=true; this.form.submit();">
                    {{ __('Simpan & Lanjutkan') }}
                </x-primary-button>
            </div>
        </form>
    </div>
@endsection
