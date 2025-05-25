{{-- resources/views/profile/partials/badges-section.blade.php --}}
@props(['user', 'isOwner'])

<div class="mb-20 space-y-6">
    <h2
        class="font-semibold text-branding-black dark:text-white text-center border-b border-gray-200 dark:border-gray-600 mb-4">
        Badges
    </h2>

    @if ($isOwner)
        <p class="text-sm text-branding-black dark:text-gray-400 text-center mb-6">
            Pilih 3 badge untuk ditampilkan di profil Anda
        </p>
    @endif

    <!-- Empty badges container for now -->
    <div class="text-center text-essentials-inactive dark:text-gray-400">
        <p>Belum ada badge yang ditampilkan</p>
    </div>

    @if ($isOwner)
        <div class="text-center">
            <x-primary-button size="xl" :disabled="true">
                Simpan
            </x-primary-button>
        </div>
    @endif
</div>
