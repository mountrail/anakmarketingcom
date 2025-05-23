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

    <form method="post" action="{{ route('account.update') }}" class="mt-6 space-y-6">
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
        <div>
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-phone-input id="phone" name="phone" :countryValue="isset($user->phone) && !empty($user->phone) ? ltrim(explode(' ', $user->phone)[0], '+') : ''" :numberValue="isset($user->phone) && strpos($user->phone, ' ') !== false ? explode(' ', $user->phone)[1] : ''" />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
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

        {{-- Industry Field --}}
        <div>
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
        <div>
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
        <div>
            <x-input-label for="company_size" :value="__('Company Size')" />
            <x-select-input id="company_size" name="company_size" :options="['0-10', '11-50', '51-100', '101-500', '501++']" :selected="old('company_size', $user->company_size)"
                :placeholder="__('--Select Company Size--')" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('company_size')" />
        </div>

        {{-- City Field --}}
        <div>
            <x-input-label for="city" :value="__('City')" />
            <x-select-input id="city" name="city" :options="['Bandung', 'Jabodetabek', 'Jogjakarta', 'Makassar', 'Medan', 'Surabaya', 'Others']" :selected="old('city', $user->city)" :placeholder="__('--Select City--')"
                class="mt-1 block w-full" />
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
