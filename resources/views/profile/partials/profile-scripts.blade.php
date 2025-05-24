{{-- resources/views/profile/partials/profile-scripts.blade.php --}}

<script>
    let hasUnsavedChanges = false;
    let originalFormData = {};

    // Store original form data
    function storeOriginalData() {
        const form = document.getElementById('profile-form');
        const formData = new FormData(form);
        originalFormData = {};

        // Store text inputs
        for (let [key, value] of formData.entries()) {
            if (key !== 'profile_picture' && key !== '_token' && key !== '_method') {
                originalFormData[key] = value;
            }
        }

        // Store current profile picture src
        originalFormData['current_profile_picture'] = document.querySelector('img[alt="{{ $user->name }}"]').src;
    }

    // Check if form data has changed
    function checkForChanges() {
        const form = document.getElementById('profile-form');
        const currentFormData = new FormData(form);
        let hasChanges = false;

        // Check text inputs
        for (let [key, value] of currentFormData.entries()) {
            if (key !== 'profile_picture' && key !== '_token' && key !== '_method') {
                if (originalFormData[key] !== value) {
                    hasChanges = true;
                    break;
                }
            }
        }

        // Check profile picture
        const currentProfilePicture = document.querySelector('img[alt="{{ $user->name }}"]').src;
        if (originalFormData['current_profile_picture'] !== currentProfilePicture) {
            hasChanges = true;
        }

        // Check if profile picture file is selected
        const fileInput = document.getElementById('hidden_profile_picture');
        if (fileInput.files.length > 0) {
            hasChanges = true;
        }

        hasUnsavedChanges = hasChanges;

        // Enable/disable save button
        const saveButton = document.getElementById('save-button');
        saveButton.disabled = !hasChanges;

        return hasChanges;
    }

    // Load more posts functionality - Show all remaining posts
    document.addEventListener('DOMContentLoaded', function() {
        const loadMoreBtn = document.getElementById('load-more-posts');

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const loaded = parseInt(this.dataset.loaded);
                const total = parseInt(this.dataset.total);
                const remaining = total - loaded; // Calculate remaining posts

                // Get current post ID if available (for excluding current post from results)
                const currentPostId = this.dataset.currentPostId || null;

                // Disable button and show loading state
                this.disabled = true;
                const originalText = this.textContent;
                this.textContent = 'Loading...';

                // Build URL with remaining count as limit to load all at once
                let url = `/profile/${userId}/posts?offset=${loaded}&limit=${remaining}`;
                if (currentPostId) {
                    url += `&current_post_id=${currentPostId}`;
                }

                // Make AJAX request
                fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.html) {
                            // Find the posts container using the load more button as reference
                            const loadMoreButton = document.getElementById('load-more-posts');
                            let postsContainer = null;

                            if (loadMoreButton) {
                                // Navigate up to find the posts section, then find the container
                                const userPostsSection = loadMoreButton.closest(
                                    '.bg-white, .dark\\:bg-gray-800');
                                if (userPostsSection) {
                                    postsContainer = userPostsSection.querySelector('.space-y-4');
                                }
                            }

                            // Fallback: try direct selector
                            if (!postsContainer) {
                                postsContainer = document.querySelector('.space-y-4');
                            }

                            if (postsContainer) {
                                postsContainer.insertAdjacentHTML('beforeend', data.html);

                                // Hide the button since we've loaded all posts
                                this.parentElement.style.display = 'none';
                            } else {
                                console.error('Posts container not found. Available containers:',
                                    Array.from(document.querySelectorAll('[class*="space"]'))
                                    .map(el => el.className)
                                );
                                throw new Error('Posts container not found');
                            }
                        } else {
                            throw new Error('Failed to load posts');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading posts:', error);
                        // Re-enable button on error
                        this.disabled = false;
                        this.textContent = originalText;
                        alert('Error loading posts. Please try again.');
                    });
            });
        }
    });
</script>
