{{-- resources\views\account\edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Akun saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('account.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('account.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('account.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    {{-- Include toast notification handler --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for Laravel session messages and show toasts
            @if (session('success'))
                toast('{{ session('success') }}', 'success', {
                    duration: 5000,
                    position: 'top-right'
                });
            @endif

            @if (session('error'))
                toast('{{ session('error') }}', 'error', {
                    duration: 6000,
                    position: 'top-right'
                });
            @endif

            @if (session('status') === 'profile-updated')
                toast('Profil berhasil diperbarui!', 'success', {
                    duration: 5000,
                    position: 'top-right'
                });
            @endif

            @if ($errors->any())
                @php
                    $errorMessages = $errors->all();
                    $firstError = $errorMessages[0];
                @endphp
                toast('{{ $firstError }}', 'error', {
                    duration: 6000,
                    position: 'top-right'
                });
            @endif
        });
    </script>
</x-app-layout>
