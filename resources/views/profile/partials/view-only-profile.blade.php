{{-- resources/views/profile/partials/view-only-profile.blade.php --}}
@props(['user'])

<div class="mb-20 space-y-6">
    <h2
        class="text-xl font-semibold text-branding-black dark:text-white text-center border-b border-gray-200 dark:border-gray-600 mb-4">
        Profil
    </h2>

    @if ($user->bio)
        <div class="mb-6">
            <p class="text-branding-black dark:text-gray-300 leading-relaxed"
                style="word-wrap: break-word; overflow-wrap: anywhere; word-break: break-word;">
                {{ $user->bio }}
            </p>
        </div>
    @else
        <div class="mb-6">
            <p class="text-essentials-inactive dark:text-gray-400 text-center">
                Pengguna belum menambahkan deskripsi profil.
            </p>
        </div>
    @endif
</div>
