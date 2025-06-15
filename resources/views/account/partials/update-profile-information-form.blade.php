{{-- resources\views\account\partials\update-profile-information-form.blade.php --}}
<section>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('account.update') }}" class="mt-6" id="account-update-form">
        @csrf
        @method('patch')

        {{-- Informasi Dasar --}}
        <div class="mb-10 space-y-6">
            <header>
                <h2 class="text-lg font-medium text-branding-primary dark:text-gray-100">
                    {{ __('Informasi Akun') }}
                </h2>
            </header>
            <div>
                <x-input-label for="name" :value="__('Nama')" />
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
                            {{ __('Alamat email Anda belum diverifikasi.') }}

                            <button form="send-verification"
                                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-branding-primary dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                {{ __('Link verifikasi baru telah dikirim ke alamat email Anda.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Nomor Telepon --}}
            <div>
                <x-input-label for="phone" :value="__('Nomor Telepon *')" />
                @php
                    // Set default country code to 62 if no phone exists
                    $defaultCountryCode = '62';
                    $defaultPhoneNumber = '';

                    if (isset($user->phone) && !empty($user->phone)) {
                        $phoneParts = explode(' ', $user->phone);
                        $defaultCountryCode = ltrim($phoneParts[0], '+');
                        $defaultPhoneNumber = isset($phoneParts[1]) ? $phoneParts[1] : '';
                    }
                @endphp
                <x-phone-input id="phone" name="phone" :countryValue="$defaultCountryCode" :numberValue="$defaultPhoneNumber" />
                <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
            </div>
        </div>

        {{-- Informasi Profesional --}}
        <div class="mb-10 space-y-6">
            <header>
                <h2 class="text-lg font-medium text-branding-primary dark:text-gray-100">
                    {{ __('Informasi Pengguna') }}
                </h2>
            </header>

            {{-- Bidang Industri --}}
            <div>
                <x-input-label for="industry" :value="__('Industri *')" />
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
                ]" :selected="old('industry', $user->industry)" :showPlaceholder="false"
                    class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('industry')" />
            </div>

            {{-- Tingkat Senioritas --}}
            <div>
                <x-input-label for="seniority" :value="__('Senioritas *')" />
                <x-select-input id="seniority" name="seniority" :options="[
                    'Junior Staff',
                    'Senior Staff',
                    'Assistant Manager',
                    'Manager',
                    'Vice President',
                    'Director (C-Level)',
                    'Owner',
                    'Others',
                ]" :selected="old('seniority', $user->seniority)" :showPlaceholder="false"
                    class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('seniority')" />
            </div>

            {{-- Jumlah Karyawan --}}
            <div>
                <x-input-label for="company_size" :value="__('Jumlah Karyawan *')" />
                <x-select-input id="company_size" name="company_size" :options="['0-10', '11-50', '51-100', '101-500', '501++']" :selected="old('company_size', $user->company_size)"
                    :showPlaceholder="false" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('company_size')" />
            </div>

            {{-- Kota --}}
            <div>
                <x-input-label for="city" :value="__('Kota *')" />
                <x-select-input id="city" name="city" :options="['Bandung', 'Jabodetabek', 'Jogjakarta', 'Makassar', 'Medan', 'Surabaya', 'Others']" :selected="old('city', $user->city)" :showPlaceholder="false"
                    class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>

            {{-- Tombol Simpan --}}
            <div class="flex items-center gap-4">
                <x-primary-button type="submit" id="save-account-button">
                    <span class="button-text">{{ __('Simpan') }}</span>
                    <span class="loading-spinner hidden">
                        <span class="inline-flex items-center">
                            <x-loading-spinner size="sm" color="white" />
                            <span class="ml-2">Menyimpan...</span>
                        </span>
                    </span>
                </x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Tersimpan.') }}
                    </p>
                @endif
            </div>
        </div>
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountForm = document.getElementById('account-update-form');
        const saveButton = document.getElementById('save-account-button');

        // Show loading state on form submission
        function showLoadingState() {
            const buttonText = saveButton.querySelector('.button-text');
            const loadingSpinner = saveButton.querySelector('.loading-spinner');

            if (buttonText) buttonText.classList.add('hidden');
            if (loadingSpinner) {
                loadingSpinner.classList.remove('hidden');
                loadingSpinner.classList.add('inline-flex', 'items-center');
            }

            saveButton.disabled = true;
        }

        // Form submission handler - only show loading state
        if (accountForm) {
            accountForm.addEventListener('submit', function(e) {
                showLoadingState();
            });
        }
    });
</script>
