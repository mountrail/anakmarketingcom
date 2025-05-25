{{-- resources/views/profile/partials/profile-header.blade.php --}}
@props(['user', 'isOwner', 'followersCount', 'followingCount'])

<div class="text-center mb-10">
    @include('profile.components.header.profile-picture', [
        'user' => $user,
        'isOwner' => $isOwner,
    ])

    @include('profile.components.header.user-info', [
        'user' => $user,
        'isOwner' => $isOwner,
    ])

    @include('profile.components.header.follow-stats', [
        'followersCount' => $followersCount,
        'followingCount' => $followingCount,
    ])

    @include('profile.components.header.follow-button', [
        'user' => $user,
        'isOwner' => $isOwner,
    ])
</div>

{{-- Include Modals --}}
@include('profile.components.modals.followers-modal', [
    'followersCount' => $followersCount,
])

@include('profile.components.modals.following-modal', [
    'followingCount' => $followingCount,
])

{{-- Include JavaScript --}}
@include('profile.components.scripts.follow-system', [
    'user' => $user,
])
