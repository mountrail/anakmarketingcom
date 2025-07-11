{{-- resources/views/profile/components/header/follow-button.blade.php --}}
@props(['user', 'isOwner'])

@if ($isOwner)
    {{-- Show Edit Profile button for profile owner --}}
    <a href="{{ route('profile.edit-profile') }}"
        class="inline-flex items-center px-4 py-2 bg-branding-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-opacity-90 focus:bg-opacity-90 active:bg-opacity-75 focus:outline-none focus:ring-2 focus:ring-branding-primary focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
        Edit Profil
    </a>
@else
    @auth
        @php
            $isFollowing = $user->isFollowedBy(auth()->user());
            $buttonText = $isFollowing ? 'Following' : 'Follow';
        @endphp

        <x-primary-button id="follow-btn" data-user-id="{{ $user->id }}"
            data-following="{{ $isFollowing ? 'true' : 'false' }}" variant="primary" size="md">
            <svg id="follow-loading" class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-current"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span id="follow-btn-text">{{ $buttonText }}</span>
        </x-primary-button>
    @else
        <x-primary-button data-auth-action="login" variant="primary" size="md">
            Follow
        </x-primary-button>
    @endauth
@endif
