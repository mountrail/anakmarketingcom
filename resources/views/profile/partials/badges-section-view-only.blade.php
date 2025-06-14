{{-- resources/views/profile/partials/badges-section-view-only.blade.php --}}
@props(['user'])

@php
    use App\Services\BadgeService;
    // Get all user badges (selected first, then unselected)
    $allUserBadges = BadgeService::getAllUserBadges($user);
@endphp

<div class="mb-20 space-y-6">
    <h2
        class="font-semibold text-branding-black dark:text-white text-center border-b border-gray-200 dark:border-gray-600 mb-4 pb-2">
        Badges
    </h2>

    @if ($allUserBadges->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            @foreach ($allUserBadges as $userProfileBadge)
                @php
                    $badge = $userProfileBadge->badge;
                    $isDisplayed = $userProfileBadge->is_displayed;
                @endphp
                <div class="flex flex-col items-center space-y-3">
                    @if ($badge->icon && file_exists(public_path('images/badges/' . $badge->icon)))
                        <img src="{{ asset('images/badges/' . $badge->icon) }}" alt="{{ $badge->name }}"
                            class="w-32 h-32 object-contain" />
                    @else
                        <x-icons.badge
                            class="w-32 h-32 {{ $isDisplayed ? 'text-yellow-500' : 'text-gray-400 dark:text-gray-600' }}" />
                    @endif
                    <div class="text-center">
                        <p class="text-sm font-semibold text-branding-black dark:text-white">{{ $badge->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $badge->description }}</p>
                        @if ($isDisplayed)
                            <span
                                class="inline-block mt-1 px-2 py-1 text-xs bg-branding-primary text-white rounded-full">
                                Ditampilkan
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-essentials-inactive dark:text-gray-400 py-8">
            <x-icons.badge class="w-32 h-32 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
            <p>Belum ada badge yang didapatkan</p>
        </div>
    @endif
</div>
