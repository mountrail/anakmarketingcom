<x-app-layout>
    <div class="flex items-center justify-center mb-6">
        <x-google-button href="{{ route('auth.google') }}">
            Continue with Google
        </x-google-button>
    </div>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')"/>
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                          autofocus autocomplete="name"/>
            <x-input-error :messages="$errors->get('name')" class="mt-2"/>
        </div>
        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')"/>
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                          required autocomplete="username"/>
            <x-input-error :messages="$errors->get('email')" class="mt-2"/>
        </div>
        <!-- Phone Number -->
        <div class="mt-4">
            <x-input-label for="phone_number" :value="__('Phone')"/>
            <x-phone-input countryName="phone_country_code" numberName="phone_number" countryId="phone_country_code"
                           numberId="phone_number" countryValue="{{ old('phone_country_code', '62') }}"
                           numberValue="{{ old('phone_number') }}"/>
            <x-input-error :messages="$errors->get('phone_country_code')" class="mt-2"/>
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2"/>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')"/>
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                          autocomplete="new-password"/>
            <x-input-error :messages="$errors->get('password')" class="mt-2"/>
        </div>
        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')"/>
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                          name="password_confirmation" required autocomplete="new-password"/>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>
        </div>

        {{-- Industry Field --}}
        <div div class="mt-4">
            <x-input-label for="industry" :value="__('Industry')"/>
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
            ]" :selected="old('industry')" :placeholder="__('--Select Industry--')"
                            class="mt-1 block w-full"/>
            <x-input-error class="mt-2" :messages="$errors->get('industry')"/>
        </div>

        {{-- Seniority Field --}}
        <div div class="mt-4">
            <x-input-label for="seniority" :value="__('Seniority')"/>
            <x-select-input id="seniority" name="seniority" :options="[
                'Junior Staff',
                'Senior Staff',
                'Assistant Manager',
                'Manager',
                'Vice President',
                'Director (C-Level)',
                'Owner',
                'Others',
            ]" :selected="old('seniority')" :placeholder="__('--Select Seniority--')"
                            class="mt-1 block w-full"/>
            <x-input-error class="mt-2" :messages="$errors->get('seniority')"/>
        </div>

        {{-- Company Size Field --}}
        <div div class="mt-4">
            <x-input-label for="company_size" :value="__('Company Size')"/>
            <x-select-input id="company_size" name="company_size"
                            :options="['0-10', '11-50', '51-100', '101-500', '501++']"
                            :selected="old('company_size')" :placeholder="__('--Select Company Size--')"
                            class="mt-1 block w-full"/>
            <x-input-error class="mt-2" :messages="$errors->get('company_size')"/>
        </div>

        {{-- City Field --}}
        <div div class="mt-4">
            <x-input-label for="city" :value="__('City')"/>
            <x-select-input id="city" name="city"
                            :options="['Bandung', 'Jabodetabek', 'Jogjakarta', 'Makassar', 'Medan', 'Surabaya', 'Others']"
                            :selected="old('city')" :placeholder="__('--Select City--')"
                            class="mt-1 block w-full"/>
            <x-input-error class="mt-2" :messages="$errors->get('city')"/>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
               href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>
            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-app-layout>
