{{-- resources/views/profile/partials/badges-section-view-only.blade.php --}}
@props(['user'])

@php
    use App\Services\BadgeService;

    // Get displayed badges (selected ones)
    $displayedBadges = BadgeService::getDisplayedBadges($user);

    // Get all user badges
    $allUserBadges = BadgeService::getAllUserBadges($user);

    // Create collections for display
    $selectedBadges = collect();
    $otherBadges = collect();

    if ($displayedBadges->count() > 0) {
        // Sort displayed badges by their display order
        $selectedBadges = $displayedBadges->sortBy('display_order')->values();

        // Get the IDs of selected badges
        $selectedBadgeIds = $displayedBadges->pluck('badge_id')->toArray();

        // Filter out selected badges from all badges and sort by date achieved (newest first)
        $otherBadges = $allUserBadges
            ->filter(function ($userBadge) use ($selectedBadgeIds) {
                return !in_array($userBadge->badge_id, $selectedBadgeIds);
            })
            ->sortByDesc('created_at');
    } else {
        // If no badges are selected, show all badges sorted by date achieved (newest first)
        $otherBadges = $allUserBadges->sortByDesc('created_at');
    }

    // Combine selected badges first, then other badges
    $badgesToShow = $selectedBadges->concat($otherBadges);
@endphp

<div class="mb-20 space-y-6">
    <h2
        class="font-semibold text-branding-black dark:text-white text-center border-b border-gray-200 dark:border-gray-600 mb-4 pb-2">
        Badges
    </h2>

    @if ($badgesToShow->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 max-w-3xl mx-auto">
            @foreach ($badgesToShow as $userProfileBadge)
                @php $badge = $userProfileBadge->badge; @endphp
                <div class="flex flex-col items-center space-y-3">
                    @if ($badge->icon && file_exists(public_path('images/badges/' . $badge->icon)))
                        <img src="{{ asset('images/badges/' . $badge->icon) }}" alt="{{ $badge->name }}"
                            class="w-24 h-24 object-contain" />
                    @else
                        <x-icons.badge class="w-24 h-24 text-yellow-500" />
                    @endif
                    <div class="text-center">
                        <p class="text-base font-semibold text-branding-black dark:text-white">{{ $badge->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $badge->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-essentials-inactive dark:text-gray-400 py-8">
            <x-icons.badge class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
            <p>Belum ada badge yang ditampilkan</p>
        </div>
    @endif
</div>
