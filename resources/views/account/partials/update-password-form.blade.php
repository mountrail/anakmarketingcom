{{-- resources\views\account\partials\update-password-form.blade.php --}}
<section>
    <header>
        <h2 class="text-lg font-medium text-branding-primary dark:text-gray-100">
            {{ __('Ubah Password') }}
        </h2>
    </header>

    <!-- Password Reset Section -->
    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
            Lupa Password Saat Ini?
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Jika Anda lupa password saat ini, kami dapat mengirimkan tautan reset password ke email Anda.
        </p>

        @if (session('password_reset_sent'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
                <p class="text-sm text-green-700">
                    ✓ Tautan reset password telah dikirim ke email Anda: {{ auth()->user()->email }}
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email.authenticated') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Kirim Tautan Reset Password
            </button>
        </form>
    </div>

    <!-- Regular Password Update Form -->
    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="current_password" :value="__('Password Saat Ini')" />
            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full"
                autocomplete="current-password" showPasswordToggle="true" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Password Baru')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full"
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button type="submit">{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
