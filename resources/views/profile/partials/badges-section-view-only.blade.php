{{-- resources/views/profile/partials/badges-section-view-only.blade.php --}}
@props(['user'])

@php
    use App\Services\BadgeService;
    $displayedBadges = BadgeService::getDisplayedBadges($user);
@endphp

<div class="mb-20 space-y-6">
    <h2
        class="font-semibold text-branding-black dark:text-white text-center border-b border-gray-200 dark:border-gray-600 mb-4 pb-2">
        Badges
    </h2>

    @if ($displayedBadges->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 max-w-2xl mx-auto">
            @foreach ($displayedBadges as $userProfileBadge)
                @php $badge = $userProfileBadge->badge; @endphp
                <div class="flex flex-col items-center space-y-3">
                    <img src="{{ asset('images/badges/' . $badge->icon) }}" alt="{{ $badge->name }}"
                        class="w-16 h-16 object-contain" />
                    <div class="text-center">
                        <p class="text-sm font-semibold text-branding-black dark:text-white">{{ $badge->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $badge->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-essentials-inactive dark:text-gray-400 py-8">
            <img src="{{ asset('images/badges/default-badge.png') }}" alt="No badges"
                class="w-16 h-16 mx-auto mb-4 opacity-30 object-contain" />
            <p>Belum ada badge yang ditampilkan</p>
        </div>
    @endif
</div>
