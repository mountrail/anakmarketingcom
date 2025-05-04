<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Basic Info --}}
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification"
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Phone --}}

        <div class="mt-6 flex space-x-2">
            <div class="w-1/4">
                <x-input-label for="phone_country_code" :value="__('Country Code')" />
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">+</span>
                    <x-text-input id="phone_country_code" name="phone_country_code" type="text"
                        class="mt-1 block w-full pl-8" placeholder="62" :value="old(
                            'phone_country_code',
                            isset($user->phone) && !empty($user->phone)
                                ? ltrim(explode(' ', $user->phone)[0], '+')
                                : '',
                        )" maxlength="3"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('phone_country_code')" />
            </div>

            <div class="w-3/4">
                <x-input-label for="phone_number" :value="__('Phone Number')" />
                <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full"
                    placeholder="8123456789" :value="old(
                        'phone_number',
                        isset($user->phone) && !empty($user->phone) && strpos($user->phone, ' ') !== false
                            ? explode(' ', $user->phone)[1]
                            : '',
                    )"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
            </div>
        </div>

        {{-- Professional Info --}}
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Professional Information') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Update your job and industry profile.') }}
            </p>
        </header>

        <div>
            <x-input-label for="industry" :value="__('Industry')" />
            <select id="industry" name="industry"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @foreach (['Beauty', 'Consumer', 'Education', 'Financial or Banking', 'Health', 'Media', 'Products', 'Property', 'Services', 'Tech', 'Others'] as $option)
                    <option value="{{ $option }}"
                        {{ old('industry', $user->industry) === $option ? 'selected' : '' }}>{{ $option }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('industry')" />
        </div>

        <div>
            <x-input-label for="seniority" :value="__('Seniority')" />
            <select id="seniority" name="seniority"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @foreach (['Junior Staff', 'Senior Staff', 'Assistant Manager', 'Manager', 'Vice President', 'Director (C-Level)', 'Owner', 'Others'] as $option)
                    <option value="{{ $option }}"
                        {{ old('seniority', $user->seniority) === $option ? 'selected' : '' }}>{{ $option }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('seniority')" />
        </div>

        <div>
            <x-input-label for="company_size" :value="__('Company Size')" />
            <select id="company_size" name="company_size"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @foreach (['0-10', '11-50', '51-100', '101-500', '501++'] as $option)
                    <option value="{{ $option }}"
                        {{ old('company_size', $user->company_size) === $option ? 'selected' : '' }}>
                        {{ $option }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('company_size')" />
        </div>

        <div>
            <x-input-label for="city" :value="__('City')" />
            <select id="city" name="city"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @foreach (['Bandung', 'Jabodetabek', 'Jogjakarta', 'Makassar', 'Medan', 'Surabaya', 'Others'] as $option)
                    <option value="{{ $option }}" {{ old('city', $user->city) === $option ? 'selected' : '' }}>
                        {{ $option }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>

        {{-- Save Button --}}
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get references to the phone input fields
        const countryCodeInput = document.getElementById('phone_country_code');
        const phoneNumberInput = document.getElementById('phone_number');

        // Auto-focus to phone number field when country code reaches max length
        countryCodeInput.addEventListener('input', function() {
            if (this.value.length >= 4) {
                phoneNumberInput.focus();
            }
        });

        // Prevent non-numeric input
        [countryCodeInput, phoneNumberInput].forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (!/^\d$/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e
                    .key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                    e.preventDefault();
                }
            });

            // Clean up on paste
            input.addEventListener('paste', function(e) {
                setTimeout(() => {
                    this.value = this.value.replace(/[^0-9]/g, '');
                }, 0);
            });
        });
    });
</script>
