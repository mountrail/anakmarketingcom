{{-- resources\views\auth\reset-password-authenticated.blade.php --}}
<x-app-layout>
    <div class="mt-24 px-6 max-w-md mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h2 class="text-xl font-semibold text-branding-primary dark:text-gray-100 mb-4">
                Reset Password
            </h2>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Masukkan password baru untuk akun: <strong>{{ auth()->user()->email }}</strong>
            </p>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email (hidden, use authenticated user's email) -->
                <input type="hidden" name="email" value="{{ auth()->user()->email }}">

                <!-- Password -->
                <div class="mb-4">
                    <x-input-label for="password" :value="__('Password Baru')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                        autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                        name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('account.edit') }}"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                        ← Kembali ke Akun
                    </a>

                    <x-primary-button type="submit">
                        Reset Password
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
