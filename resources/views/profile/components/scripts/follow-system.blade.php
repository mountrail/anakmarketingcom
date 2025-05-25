{{-- resources/views/profile/components/scripts/follow-system.blade.php --}}
@props(['user'])

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

        // Modal functions
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
