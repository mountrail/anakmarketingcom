{{-- resources\views\home\partials\post-list.blade.php --}}
<div>
    <div class="flex flex-col items-center space-x-2 mt-4">
        <div class="inline-flex items-center my-1 px-4 py-2 ">
            <a href="{{ Auth::check() ? route('posts.create') : '#' }}"
                @if (!Auth::check()) data-auth-action="login" @endif
                class="px-6 py-2 bg-branding-primary text-branding-light rounded-md text-xl font-bold shadow-md">
                {{ __('Mulai Pertanyaan / Diskusi') }}
            </a>
        </div>

        <div class="flex items-center my-1">
            <x-dropdown align="center" width="trigger">
                <x-slot name="trigger">
                    <button
                        class="flex items-center w-52 rounded-md font-bold px-5 py-2.5 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:border-secondary-pale focus:ring-secondary-pale shadow-md">
                        <span class="text-lg">{{ $selectedType == 'question' ? 'Pertanyaan' : 'Diskusi' }}</span>
                        <svg class="ms-auto h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="py-1">
                        <a href="{{ route('posts.questions') }}"
                            class="block w-full px-4 py-2 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                            Pertanyaan
                        </a>
                        <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                        <a href="{{ route('posts.discussions') }}"
                            class="block w-full px-4 py-2 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                            Diskusi
                        </a>
                    </div>
                </x-slot>
            </x-dropdown>
        </div>
    </div>

    <div class="py-2">
        <!-- Editor's Picks Section -->
        @if (isset($typedEditorPicks) && $typedEditorPicks->count() > 0)
            <div>
                @foreach ($typedEditorPicks as $pick)
                    <x-post-item :post="$pick" :isHighlighted="true" :showVoteScore="false" :showCommentCount="true"
                        :showShare="true" />
                @endforeach
            </div>
        @endif

        <!-- Latest Posts -->
        <div id="posts-container">
            @if ($posts->isEmpty() && (!isset($typedEditorPicks) || $typedEditorPicks->count() == 0))
                <div class="bg-white dark:bg-gray-800 border-b border-essentials-inactive">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <p class="text-center">Belum ada
                            {{ $selectedType == 'question' ? 'pertanyaan' : 'diskusi' }}
                            yang tersedia.</p>
                    </div>
                </div>
            @elseif ($posts->count() > 0 || (isset($typedEditorPicks) && $typedEditorPicks->count() > 0))
                <div id="posts-list">
                    @foreach ($posts as $post)
                        <x-post-item :post="$post" :showVoteScore="false" :showCommentCount="true" :showShare="true" />
                    @endforeach
                </div>

                <!-- Skeleton Loading -->
                <div id="skeleton-loading" class="hidden">
                    @for ($i = 0; $i < 3; $i++)
                        <div
                            class="bg-white dark:bg-gray-800 p-4 px-6 border-b border-essentials-inactive animate-pulse">
                            <div class="flex items-center py-2 space-x-4">
                                <div class="h-3 bg-gray-300 dark:bg-gray-600 rounded w-16"></div>
                                <div class="h-3 bg-gray-300 dark:bg-gray-600 rounded w-20"></div>
                                <div class="h-3 bg-gray-300 dark:bg-gray-600 rounded w-12"></div>
                            </div>
                            <div class="h-5 bg-gray-300 dark:bg-gray-600 rounded w-3/4 mb-2"></div>
                            <div class="space-y-2 mt-2">
                                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-full"></div>
                                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-5/6"></div>
                                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-4/5"></div>
                            </div>
                            <div class="flex items-center mt-3 space-x-4">
                                <div class="h-8 bg-gray-300 dark:bg-gray-600 rounded w-16"></div>
                                <div class="h-8 bg-gray-300 dark:bg-gray-600 rounded w-16"></div>
                                <div class="h-8 bg-gray-300 dark:bg-gray-600 rounded w-12"></div>
                                <div class="h-8 bg-gray-300 dark:bg-gray-600 rounded w-12"></div>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- Load More Trigger -->
                <div id="load-more-trigger" class="h-10"
                    data-has-more="{{ $posts->hasMorePages() ? 'true' : 'false' }}" data-next-page="2"
                    data-type="{{ $selectedType }}"></div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Only show flash messages as toasts on the home/index page
            if (window.location.pathname === '/' || window.location.pathname.includes('/posts') && !window.location
                .pathname.includes('/posts/')) {
                @if (session('success'))
                    toast('{{ session('success') }}', 'success');
                @endif

                @if (session('error'))
                    toast('{{ session('error') }}', 'error');
                @endif

                @if (session('info'))
                    toast('{{ session('info') }}', 'info');
                @endif
            }

            // Infinite Scroll Implementation
            let isLoading = false;
            const loadMoreTrigger = document.getElementById('load-more-trigger');
            const skeletonLoading = document.getElementById('skeleton-loading');
            const postsList = document.getElementById('posts-list');

            if (loadMoreTrigger) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !isLoading && loadMoreTrigger.dataset
                            .hasMore === 'true') {
                            loadMorePosts();
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '100px'
                });

                observer.observe(loadMoreTrigger);
            }

            function loadMorePosts() {
                if (isLoading) return;

                isLoading = true;
                skeletonLoading.classList.remove('hidden');

                const nextPage = parseInt(loadMoreTrigger.dataset.nextPage);
                const postType = loadMoreTrigger.dataset.type;

                fetch(`{{ route('posts.load-more') }}?page=${nextPage}&type=${postType}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.html) {
                            postsList.insertAdjacentHTML('beforeend', data.html);
                            loadMoreTrigger.dataset.nextPage = data.nextPage;
                            loadMoreTrigger.dataset.hasMore = data.hasMore ? 'true' : 'false';

                            if (!data.hasMore) {
                                loadMoreTrigger.style.display = 'none';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading more posts:', error);
                        toast('Error loading more posts', 'error');
                    })
                    .finally(() => {
                        isLoading = false;
                        skeletonLoading.classList.add('hidden');
                    });
            }

            // Handle AJAX responses for editor's pick actions
            document.addEventListener('click', function(e) {
                if (e.target.matches('[data-action="toggle-featured"]')) {
                    e.preventDefault();

                    const button = e.target;
                    const form = button.closest('form');
                    const postId = button.dataset.postId;
                    const postTitle = button.dataset.postTitle;

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const isFeatured = data.is_featured;
                                const message = isFeatured ?
                                    `"${postTitle}" has been added to Editor's Picks` :
                                    `"${postTitle}" has been removed from Editor's Picks`;

                                toast(message, 'success');

                                button.innerHTML = isFeatured ?
                                    '<i class="fas fa-star"></i> Remove from Editor\'s Pick' :
                                    '<i class="far fa-star"></i> Add to Editor\'s Pick';
                            } else {
                                toast(data.message || 'An error occurred', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            toast('An error occurred while updating the post', 'error');
                        });
                }

                // Handle post deletion
                if (e.target.matches('[data-action="delete-post"]')) {
                    e.preventDefault();

                    const button = e.target;
                    const form = button.closest('form');
                    const postTitle = button.dataset.postTitle;

                    if (confirm(`Are you sure you want to delete "${postTitle}"?`)) {
                        fetch(form.action, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    toast(`"${postTitle}" has been deleted successfully`, 'success');

                                    const postItem = button.closest('[data-post-item]');
                                    if (postItem) {
                                        postItem.style.transition = 'opacity 0.3s ease';
                                        postItem.style.opacity = '0';
                                        setTimeout(() => postItem.remove(), 300);
                                    }
                                } else {
                                    toast(data.message || 'Failed to delete post', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                toast('An error occurred while deleting the post', 'error');
                            });
                    }
                }
            });
        });
    </script>
@endpush
