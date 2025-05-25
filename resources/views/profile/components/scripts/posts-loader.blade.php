{{-- resources/views/profile/components/scripts/posts-loader.blade.php --}}
@props(['user'])

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load more posts functionality
        const loadMoreBtn = document.getElementById('load-more-posts');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const loaded = parseInt(this.dataset.loaded);
                const total = parseInt(this.dataset.total);
                const remaining = total - loaded;

                // Get current post ID if available
                const currentPostId = this.dataset.currentPostId || null;

                // Disable button and show loading state
                this.disabled = true;
                const originalText = this.textContent;
                this.textContent = 'Loading...';

                // Build URL
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
                            // Find the posts container
                            const loadMoreButton = document.getElementById('load-more-posts');
                            let postsContainer = null;

                            if (loadMoreButton) {
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
                                console.error('Posts container not found');
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
