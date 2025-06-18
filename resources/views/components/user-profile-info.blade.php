{{-- resources/views/components/user-profile-info.blade.php --}}
@props([
    'user',
    'timestamp',
    'badgeSize' => 'w-7 h-7', // Default badge size, can be overridden
    'profileSize' => 'h-10 w-10', // Default profile image size
    'showJobInfo' => true,
    'additionalBadges' => null, // For any special badges like Editor's Pick
    'mobileBadgeSize' => null, // Optional mobile badge size - if null, uses same as desktop
])

@php
    use App\Services\BadgeService;
    $displayedBadges = BadgeService::getDisplayedBadges($user);
@endphp

<div class="flex justify-between items-start gap-3">
    <!-- Left side: Profile Picture and User Info -->
    <div class="flex items-center space-x-3 min-w-0 flex-1">
        <!-- Profile Picture -->
        <a href="{{ route('profile.show', $user) }}" class="flex-shrink-0">
            <img src="{{ $user->getProfileImageUrl() }}" alt="{{ $user->name }}"
                class="{{ $profileSize }} rounded-full object-cover hover:opacity-80 transition-opacity">
        </a>

        <!-- Name, Job Info, and Time -->
        <div class="flex flex-col min-w-0 flex-1">
            <a href="{{ route('profile.show', $user) }}"
                class="font-medium text-gray-900 dark:text-gray-100 hover:text-branding-primary dark:hover:text-branding-primary transition-colors truncate">
                {{ $user->name }}
            </a>

            @if ($showJobInfo)
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    @if ($user->job_title && $user->company)
                        {{ $user->job_title }} at {{ $user->company }}
                    @elseif($user->job_title)
                        {{ $user->job_title }}
                    @elseif($user->company)
                        {{ $user->company }}
                    @else
                        Member
                    @endif
                </div>
            @endif

            @if ($timestamp)
                <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ $timestamp->diffForHumans() }}
                </span>
            @endif
        </div>
    </div>

    <!-- Right side: Additional Badges and User Badges -->
    <div class="flex items-center space-x-2 flex-shrink-0 self-center">
        <!-- Additional badges (like Editor's Pick) -->
        @if ($additionalBadges)
            {{ $additionalBadges }}
        @endif

        <!-- User Achievement Badges -->
        @if ($displayedBadges->count() > 0)
            <x-badge-preview :badges="$displayedBadges" :user="$user" :badgeSize="$badgeSize" :mobileBadgeSize="$mobileBadgeSize" />
        @endif
    </div>
</div>
