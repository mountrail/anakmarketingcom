@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="mt-24 px-6 max-w-md mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-branding-primary dark:text-white mb-2">
                Lengkapi Informasi Profesional
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Bantu kami mengenal Anda lebih baik dengan melengkapi informasi berikut
            </p>
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
                <x-input-label for="industry" :value="__('Industri')" />
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
                ]" :selected="old('industry', $user->industry)" :placeholder="__('--Pilih Industri--')"
                    class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('industry')" />
            </div>

            {{-- Seniority Field --}}
            <div class="mb-4">
                <x-input-label for="seniority" :value="__('Senioritas')" />
                <x-select-input id="seniority" name="seniority" :options="[
                    'Junior Staff',
                    'Senior Staff',
                    'Assistant Manager',
                    'Manager',
                    'Vice President',
                    'Director (C-Level)',
                    'Owner',
                    'Others',
                ]" :selected="old('seniority', $user->seniority)" :placeholder="__('--Pilih Senioritas--')"
                    class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('seniority')" />
            </div>

            {{-- Company Size Field --}}
            <div class="mb-4">
                <x-input-label for="company_size" :value="__('Ukuran Perusahaan')" />
                <x-select-input id="company_size" name="company_size" :options="['0-10', '11-50', '51-100', '101-500', '501++']" :selected="old('company_size', $user->company_size)"
                    :placeholder="__('--Pilih Ukuran Perusahaan--')" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('company_size')" />
            </div>

            {{-- City Field --}}
            <div class="mb-6">
                <x-input-label for="city" :value="__('Kota')" />
                <x-select-input id="city" name="city" :options="['Bandung', 'Jabodetabek', 'Jogjakarta', 'Makassar', 'Medan', 'Surabaya', 'Others']" :selected="old('city', $user->city)" :placeholder="__('--Pilih Kota--')"
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
