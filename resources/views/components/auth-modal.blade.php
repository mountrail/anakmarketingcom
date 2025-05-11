<!-- resources/views/components/auth-modal.blade.php -->
<div x-data="{
    activeTab: '{{ $activeTab ?? 'login' }}',
    showModal: false,
    showRegisterForm: false,
    showLoginForm: false,
    registrationStep: 1
}" @keydown.escape.window="showModal = false"
    @open-auth-modal.window="showModal = true; activeTab = $event.detail || '{{ $activeTab ?? 'login' }}'">

    <!-- Modal Background -->
    <div x-cloak x-show="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <!-- Modal Content -->
        <div @click.away="showModal = false" x-show="showModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-full">

            <!-- Tabs Header -->
            <div class="flex text-center">
                <div @click="activeTab = 'register'; registrationStep = 1;"
                    :class="{ 'bg-orange-100 dark:bg-orange-900 text-orange-500': activeTab === 'register', 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': activeTab !== 'register' }"
                    class="w-1/2 py-3 px-4 font-medium cursor-pointer transition-colors">
                    Sign Up
                </div>
                <div @click="activeTab = 'login'"
                    :class="{ 'bg-orange-100 dark:bg-orange-900 text-orange-500': activeTab === 'login', 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': activeTab !== 'login' }"
                    class="w-1/2 py-3 px-4 font-medium cursor-pointer transition-colors">
                    Login
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-6">
                <!-- Register Header -->
                <h2 class="text-4xl font-bold text-center text-orange-500 mb-6" x-show="activeTab === 'register' && registrationStep === 1">
                    Sign Up
                </h2>
                <h2 class="text-4xl font-bold text-center text-orange-500 mb-6" x-show="activeTab === 'register' && registrationStep === 2">
                    One Last Step
                </h2>

                <!-- Login Header -->
                <h2 class="text-4xl font-bold text-center text-orange-500 mb-6" x-show="activeTab === 'login'">
                    Login
                </h2>

                <div x-show="activeTab === 'register'">
                    <!-- Register Content -->
                    <div>
                        <div class="flex items-center justify-center mb-6" x-show="registrationStep === 1">
                            <a href="{{ route('auth.google') }}"
                                class="w-full bg-blue-500 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z"
                                        fill="currentColor" />
                                </svg>
                                Continue with Google
                            </a>
                        </div>

                        <div @click="showRegisterForm = !showRegisterForm" x-show="registrationStep === 1"
                            class="w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold py-3 px-4 rounded text-center cursor-pointer mb-6">
                            Register with Email
                        </div>

                        <div x-show="showRegisterForm || registrationStep === 2" class="mt-4">
                            <!-- Registration form with step control -->
                            <form method="POST" action="{{ route('register') }}" id="register-form"
                                  data-validate-step1="true">
                                @csrf
                                <!-- Step 1 Fields: Personal Information -->
                                <div x-show="registrationStep === 1">
                                    <!-- Name -->
                                    <div>
                                        <x-input-label for="register_name" :value="__('Name')" />
                                        <x-text-input id="register_name" class="block mt-1 w-full" type="text"
                                            name="name" :value="old('name')" required autofocus autocomplete="name" />
                                        <div id="name-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>

                                    <!-- Email Address -->
                                    <div class="mt-4">
                                        <x-input-label for="register_email" :value="__('Email')" />
                                        <x-text-input id="register_email" class="block mt-1 w-full" type="email"
                                            name="email" :value="old('email')" required autocomplete="username" />
                                        <div id="email-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                    </div>

                                    <!-- Phone Number -->
                                    <div class="mt-4">
                                        <x-input-label for="register_phone_number" :value="__('Phone')" />
                                        <x-phone-input countryName="phone_country_code" numberName="phone_number"
                                            countryId="register_phone_country_code" numberId="register_phone_number"
                                            countryValue="{{ old('phone_country_code', '62') }}"
                                            numberValue="{{ old('phone_number') }}" />
                                        <div id="phone_country_code-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <div id="phone_number-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <x-input-error :messages="$errors->get('phone_country_code')" class="mt-2" />
                                        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                                    </div>

                                    <!-- Password -->
                                    <div class="mt-4">
                                        <x-input-label for="register_password" :value="__('Password')" />
                                        <x-text-input id="register_password" class="block mt-1 w-full" type="password"
                                            name="password" required autocomplete="new-password" />
                                        <div id="password-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="mt-4">
                                        <x-input-label for="register_password_confirmation" :value="__('Confirm Password')" />
                                        <x-text-input id="register_password_confirmation" class="block mt-1 w-full"
                                            type="password" name="password_confirmation" required
                                            autocomplete="new-password" />
                                        <div id="password_confirmation-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>
                                </div>

                                <!-- Step 2 Fields: Professional Information -->
                                <div x-show="registrationStep === 2">
                                    {{-- Industry Field --}}
                                    <div class="mt-4">
                                        <x-input-label for="register_industry" :value="__('Industry')" />
                                        <x-select-input id="register_industry" name="industry" :options="[
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
                                        ]" :selected="old('industry')" :placeholder="__('--Select Industry--')" class="mt-1 block w-full" />
                                        <div id="industry-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <x-input-error class="mt-2" :messages="$errors->get('industry')" />
                                    </div>

                                    {{-- Seniority Field --}}
                                    <div class="mt-4">
                                        <x-input-label for="register_seniority" :value="__('Seniority')" />
                                        <x-select-input id="register_seniority" name="seniority" :options="[
                                            'Junior Staff',
                                            'Senior Staff',
                                            'Assistant Manager',
                                            'Manager',
                                            'Vice President',
                                            'Director (C-Level)',
                                            'Owner',
                                            'Others',
                                        ]" :selected="old('seniority')" :placeholder="__('--Select Seniority--')" class="mt-1 block w-full" />
                                        <div id="seniority-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <x-input-error class="mt-2" :messages="$errors->get('seniority')" />
                                    </div>

                                    {{-- Company Size Field --}}
                                    <div class="mt-4">
                                        <x-input-label for="register_company_size" :value="__('Company Size')" />
                                        <x-select-input id="register_company_size" name="company_size"
                                            :options="['0-10', '11-50', '51-100', '101-500', '501++']"
                                            :selected="old('company_size')" :placeholder="__('--Select Company Size--')" class="mt-1 block w-full" />
                                        <div id="company_size-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <x-input-error class="mt-2" :messages="$errors->get('company_size')" />
                                    </div>

                                    {{-- City Field --}}
                                    <div class="mt-4">
                                        <x-input-label for="register_city" :value="__('City')" />
                                        <x-select-input id="register_city" name="city"
                                            :options="['Bandung', 'Jabodetabek', 'Jogjakarta', 'Makassar', 'Medan', 'Surabaya', 'Others']"
                                            :selected="old('city')" :placeholder="__('--Select City--')" class="mt-1 block w-full" />
                                        <div id="city-error" class="text-red-500 mt-1 text-sm hidden"></div>
                                        <x-input-error class="mt-2" :messages="$errors->get('city')" />
                                    </div>

                                    <!-- Back Button - Return to Step 1 -->
                                    <div class="flex items-center justify-start mt-4">
                                        <button type="button" @click="registrationStep = 1"
                                            class="text-sm text-orange-500 hover:text-orange-600 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                            Back to personal details
                                        </button>
                                    </div>
                                </div>

                                <!-- Form Footer -->
                                <div class="flex items-center justify-end mt-4">
                                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                        href="#" @click.prevent="activeTab = 'login'">
                                        {{ __('Already registered?') }}
                                    </a>

                                    <x-primary-button class="ms-4 bg-orange-500 hover:bg-orange-600" x-text="registrationStep === 1 ? 'Continue' : 'Register'">
                                        {{ __('Continue') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'login'">
                    <!-- Login Content -->
                    <div>
                        <div class="flex items-center justify-center mb-6">
                            <a href="{{ route('auth.google') }}"
                                class="w-full bg-blue-500 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z"
                                        fill="currentColor" />
                                </svg>
                                Login with Google
                            </a>
                        </div>

                        <div @click="showLoginForm = !showLoginForm"
                            class="w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold py-3 px-4 rounded text-center cursor-pointer mb-6">
                            Login with Email
                        </div>

                        <div x-show="showLoginForm" class="mt-4">
                            <!-- Login form -->
                            <form method="POST" action="{{ route('login') }}" id="login-form">
                                @csrf

                                <!-- Email Address -->
                                <div>
                                    <x-input-label for="login_email" :value="__('Email')" />
                                    <x-text-input id="login_email" class="block mt-1 w-full" type="email"
                                        name="email" :value="old('email')" required autofocus
                                        autocomplete="username" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <!-- Password -->
                                <div class="mt-4">
                                    <x-input-label for="login_password" :value="__('Password')" />

                                    <x-text-input id="login_password" class="block mt-1 w-full" type="password"
                                        name="password" required autocomplete="current-password" />

                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <!-- Remember Me -->
                                <div class="block mt-4">
                                    <label for="remember_me" class="inline-flex items-center">
                                        <input id="remember_me" type="checkbox"
                                            class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                                            name="remember">
                                        <span
                                            class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                                    </label>
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    @if (Route::has('password.request'))
                                        <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                            href="{{ route('password.request') }}">
                                            {{ __('Forgot your password?') }}
                                        </a>
                                    @endif

                                    <x-primary-button class="ms-3 bg-orange-500 hover:bg-orange-600">
                                        {{ __('Log in') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
