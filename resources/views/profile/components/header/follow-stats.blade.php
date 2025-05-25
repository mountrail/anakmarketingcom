{{-- resources/views/profile/components/header/follow-stats.blade.php --}}
@props(['followersCount', 'followingCount'])

<div class="flex justify-center space-x-6 mb-4 text-sm text-gray-600 dark:text-gray-400">
    <button onclick="openFollowersModal()" class="hover:text-branding-primary transition-colors cursor-pointer">
        <span class="font-semibold" id="followers-count">{{ $followersCount }}</span> Followers
    </button>
    <button onclick="openFollowingModal()" class="hover:text-branding-primary transition-colors cursor-pointer">
        <span class="font-semibold">{{ $followingCount }}</span> Following
    </button>
</div>
