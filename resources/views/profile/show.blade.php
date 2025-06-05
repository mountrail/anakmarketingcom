{{-- resources/views/profile/show.blade.php --}}

<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-brandtext-branding-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            @include('profile.partials.profile-header', [
                'user' => $user,
                'isOwner' => $isOwner,
                'followers' => $followers,
                'following' => $following,
                'followersCount' => $followersCount,
                'followingCount' => $followingCount,
            ])

            <div>
                {{-- Always show view-only profile information --}}
                @include('profile.partials.view-only-profile', [
                    'user' => $user,
                ])

                {{-- Always show view-only badges section --}}
                @include('profile.partials.badges-section-view-only', [
                    'user' => $user,
                ])

                @include('profile.partials.user-posts', [
                    'user' => $user,
                    'limit' => 2,
                    'showToGuestOnly' => false,
                    'isOwner' => $isOwner,
                ])
            </div>
        </div>
    </div>

    @include('profile.partials.profile-scripts')
</x-app-layout>
