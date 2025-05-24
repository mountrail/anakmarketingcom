{{-- resources/views/profile/partials/profile-header.blade.php --}}
@props(['user', 'isOwner', 'followersCount', 'followingCount'])

<div class="text-center mb-10">
    <div class="relative inline-block">
        <img src="{{ $user->getProfileImageUrl() }}" alt="{{ $user->name }}"
            class="w-32 h-32 rounded-full mx-auto mb-4 object-cover object-center border-4 border-white shadow-lg"
            style="aspect-ratio: 1/1;">

        @if ($isOwner)
            <x-primary-button onclick="document.getElementById('profile_picture_input').click()" variant="primary"
                size="sm" class="mb-8">
                Upload Foto
            </x-primary-button>
            <input type="file" id="profile_picture_input" name="profile_picture" accept="image/*" class="hidden">
        @endif
    </div>

    @if (!$isOwner)
        <h1 class="font-bold text-branding-black dark:text-white">{{ $user->name }}</h1>
        @if ($user->job_title || $user->company)
            <p class="text-xl text-branding-black dark:text-gray-400 mb-4">
                @if ($user->job_title)
                    {{ $user->job_title }}
                @endif
                @if ($user->job_title && $user->company)
                    <br> at
                @endif
                @if ($user->company)
                    {{ $user->company }}
                @endif
            </p>
        @endif
    @endif

    {{-- Follow Stats --}}
    <div class="flex justify-center space-x-6 mb-4 text-sm text-gray-600 dark:text-gray-400">
        <button onclick="openFollowersModal()" class="hover:text-branding-primary transition-colors cursor-pointer">
            <span class="font-semibold" id="followers-count">{{ $followersCount }}</span> Followers
        </button>
        <button onclick="openFollowingModal()" class="hover:text-branding-primary transition-colors cursor-pointer">
            <span class="font-semibold">{{ $followingCount }}</span> Following
        </button>
    </div>

    {{-- Follow Button --}}
    @if (!$isOwner)
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
            <x-primary-button onclick="window.location.href='{{ route('login') }}'" variant="primary" size="md">
                Follow
            </x-primary-button>
        @endauth
    @endif
</div>

{{-- Followers Modal --}}
<div id="followers-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="closeFollowersModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Followers (<span id="followers-modal-count">{{ $followersCount }}</span>)
                    </h3>
                    <button onclick="closeFollowersModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="max-h-96 overflow-y-auto" id="followers-content">
                    {{-- Loading state --}}
                    <div id="followers-loading" class="flex justify-center items-center py-8">
                        <svg class="animate-spin h-8 w-8 text-branding-primary" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="ml-2 text-gray-600 dark:text-gray-400">Loading followers...</span>
                    </div>
                    {{-- Content will be loaded here --}}
                    <div id="followers-list" class="hidden"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Following Modal --}}
<div id="following-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="closeFollowingModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Following (<span id="following-modal-count">{{ $followingCount }}</span>)
                    </h3>
                    <button onclick="closeFollowingModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="max-h-96 overflow-y-auto" id="following-content">
                    {{-- Loading state --}}
                    <div id="following-loading" class="flex justify-center items-center py-8">
                        <svg class="animate-spin h-8 w-8 text-branding-primary" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="ml-2 text-gray-600 dark:text-gray-400">Loading following...</span>
                    </div>
                    {{-- Content will be loaded here --}}
                    <div id="following-list" class="hidden"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Follow Button JavaScript --}}
@auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const followBtn = document.getElementById('follow-btn');
            const followBtnText = document.getElementById('follow-btn-text');
            const followLoading = document.getElementById('follow-loading');
            const followersCount = document.getElementById('followers-count');

            if (followBtn) {
                followBtn.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    const isCurrentlyFollowing = this.dataset.following === 'true';

                    // Show loading state
                    followLoading.classList.remove('hidden');
                    followBtn.disabled = true;

                    // Make AJAX request
                    fetch(`/follow/${userId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Update button text and state
                                followBtnText.textContent = data.buttonText;
                                followBtn.dataset.following = data.isFollowing ? 'true' : 'false';

                                // Update followers count
                                followersCount.textContent = data.followersCount;

                                // Update modal count if visible
                                const followersModalCount = document.getElementById(
                                    'followers-modal-count');
                                if (followersModalCount) {
                                    followersModalCount.textContent = data.followersCount;
                                }
                            } else {
                                console.error('Error:', data.error);
                                alert(data.error || 'Something went wrong');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Something went wrong. Please try again.');
                        })
                        .finally(() => {
                            // Hide loading state
                            followLoading.classList.add('hidden');
                            followBtn.disabled = false;
                        });
                });

                // Add hover effect for "Following" button
                followBtn.addEventListener('mouseenter', function() {
                    if (this.dataset.following === 'true') {
                        followBtnText.textContent = 'Unfollow';
                    }
                });

                followBtn.addEventListener('mouseleave', function() {
                    if (this.dataset.following === 'true') {
                        followBtnText.textContent = 'Following';
                    }
                });
            }
        });

        // Async modal functions
        async function openFollowersModal() {
            const modal = document.getElementById('followers-modal');
            const loadingDiv = document.getElementById('followers-loading');
            const listDiv = document.getElementById('followers-list');

            // Show modal
            modal.classList.remove('hidden');

            // Show loading, hide list
            loadingDiv.classList.remove('hidden');
            listDiv.classList.add('hidden');
            listDiv.innerHTML = '';

            try {
                const userId = {{ $user->id }};
                const response = await fetch(`/follow/${userId}/followers`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Hide loading
                    loadingDiv.classList.add('hidden');

                    // Build and show content
                    if (data.followers && data.followers.length > 0) {
                        let html = '';
                        data.followers.forEach(follower => {
                            html += `
                                <div class="flex items-center justify-between py-3 px-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <a href="${follower.profile_url}">
                                            <img src="${follower.profile_image}" alt="${follower.name}"
                                                class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                                        </a>
                                        <div>
                                            <a href="${follower.profile_url}"
                                                class="font-medium text-gray-900 dark:text-white hover:text-branding-primary">
                                                ${follower.name}
                                            </a>
                                            ${follower.job_title || follower.company ? `
                                                        <p class="text-sm text-gray-500">
                                                            ${follower.job_title || ''}
                                                            ${follower.job_title && follower.company ? ' at ' : ''}
                                                            ${follower.company || ''}
                                                        </p>
                                                    ` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        if (data.total > 50) {
                            html += `
                                <div class="text-center py-2 text-sm text-gray-500">
                                    Showing first 50 of ${data.total} followers
                                </div>
                            `;
                        }

                        listDiv.innerHTML = html;
                    } else {
                        listDiv.innerHTML = '<div class="text-center py-4 text-gray-500">No followers yet</div>';
                    }

                    // Show list
                    listDiv.classList.remove('hidden');
                } else {
                    throw new Error(data.error || 'Failed to load followers');
                }
            } catch (error) {
                console.error('Error loading followers:', error);
                loadingDiv.classList.add('hidden');
                listDiv.innerHTML =
                    '<div class="text-center py-4 text-red-500">Error loading followers. Please try again.</div>';
                listDiv.classList.remove('hidden');
            }
        }

        async function openFollowingModal() {
            const modal = document.getElementById('following-modal');
            const loadingDiv = document.getElementById('following-loading');
            const listDiv = document.getElementById('following-list');

            // Show modal
            modal.classList.remove('hidden');

            // Show loading, hide list
            loadingDiv.classList.remove('hidden');
            listDiv.classList.add('hidden');
            listDiv.innerHTML = '';

            try {
                const userId = {{ $user->id }};
                const response = await fetch(`/follow/${userId}/following`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Hide loading
                    loadingDiv.classList.add('hidden');

                    // Build and show content
                    if (data.following && data.following.length > 0) {
                        let html = '';
                        data.following.forEach(followedUser => {
                            html += `
                                <div class="flex items-center justify-between py-3 px-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <a href="${followedUser.profile_url}">
                                            <img src="${followedUser.profile_image}" alt="${followedUser.name}"
                                                class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                                        </a>
                                        <div>
                                            <a href="${followedUser.profile_url}"
                                                class="font-medium text-gray-900 dark:text-white hover:text-branding-primary">
                                                ${followedUser.name}
                                            </a>
                                            ${followedUser.job_title || followedUser.company ? `
                                                        <p class="text-sm text-gray-500">
                                                            ${followedUser.job_title || ''}
                                                            ${followedUser.job_title && followedUser.company ? ' at ' : ''}
                                                            ${followedUser.company || ''}
                                                        </p>
                                                    ` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        if (data.total > 50) {
                            html += `
                                <div class="text-center py-2 text-sm text-gray-500">
                                    Showing first 50 of ${data.total} following
                                </div>
                            `;
                        }

                        listDiv.innerHTML = html;
                    } else {
                        listDiv.innerHTML =
                            '<div class="text-center py-4 text-gray-500">Not following anyone yet</div>';
                    }

                    // Show list
                    listDiv.classList.remove('hidden');
                } else {
                    throw new Error(data.error || 'Failed to load following');
                }
            } catch (error) {
                console.error('Error loading following:', error);
                loadingDiv.classList.add('hidden');
                listDiv.innerHTML =
                    '<div class="text-center py-4 text-red-500">Error loading following. Please try again.</div>';
                listDiv.classList.remove('hidden');
            }
        }

        function closeFollowersModal() {
            document.getElementById('followers-modal').classList.add('hidden');
        }

        function closeFollowingModal() {
            document.getElementById('following-modal').classList.add('hidden');
        }
    </script>
@endauth
