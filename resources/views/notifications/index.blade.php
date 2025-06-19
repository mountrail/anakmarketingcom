{{-- resources/views/notifications/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800">
                <div class="text-gray-900 dark:text-gray-100">

                    {{-- Dynamic Header with JavaScript Filtering --}}
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            Pusat Notifikasi
                        </h1>

                        {{-- Category Filter Dropdown --}}
                        <div class="flex justify-center">
                            <x-dropdown align="center" width="64">
                                <x-slot name="trigger">
                                    <button id="categoryTrigger"
                                        class="flex items-center w-80 rounded-md font-medium px-5 py-2.5 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:border-secondary-pale focus:ring-secondary-pale shadow-md">
                                        <span class="text-lg" id="selectedCategory">Semua</span>
                                        <svg class="ms-auto h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="py-1 max-h-none w-max overflow-visible">
                                        <button data-category="Semua"
                                            class="category-filter block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out bg-gray-100 dark:bg-gray-800">
                                            Semua
                                        </button>
                                        <button data-category="Pertanyaan / Diskusi Saya"
                                            class="category-filter block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                                            Pertanyaan / Diskusi Saya
                                        </button>
                                        <button data-category="Pertanyaan / Diskusi yang Diikuti"
                                            class="category-filter block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                                            Pertanyaan / Diskusi yang Diikuti
                                        </button>
                                        <button data-category="Lainnya"
                                            class="category-filter block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                                            Lainnya
                                        </button>
                                    </div>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    {{-- Delete All Button (only show if there are non-system notifications) --}}
                    @php
                        $hasNonSystemNotifications = false;
                        $allNotifications = $pinnedNotifications->merge($regularNotifications);
                        foreach ($allNotifications as $notification) {
                            if (($notification->data['category'] ?? null) !== 'system') {
                                $hasNonSystemNotifications = true;
                                break;
                            }
                        }
                    @endphp

                    @if ($hasNonSystemNotifications)
                        @include('notifications.partials.delete-all-button')
                    @endif

                    {{-- Check if we have any notifications at all --}}
                    @if ($pinnedNotifications->count() > 0 || $regularNotifications->count() > 0)

                        {{-- Pinned Notifications List --}}
                        @if ($pinnedNotifications->count() > 0)
                            <div class="pinned-notifications mb-4">
                                @foreach ($pinnedNotifications as $notification)
                                    @include('notifications.partials.item', [
                                        'notification' => $notification,
                                        'unreadNotificationIds' => $unreadNotificationIds,
                                    ])
                                @endforeach
                            </div>
                        @endif

                        {{-- Custom Notifications (System Posts) --}}
                        @if ($customNotifications->count() > 0)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">System Notifications</h3>

                                @foreach ($customNotifications as $notification)
                                    <div
                                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-3 @if ($notification->is_pinned) border-l-4 border-l-blue-500 @endif">
                                        <div class="flex items-start space-x-3">
                                            <img src="{{ $notification->getAvatarUrl() }}" alt="Avatar"
                                                class="w-10 h-10 rounded-full">

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <h4 class="text-sm font-semibold text-gray-900">
                                                        {{ $notification->title }}
                                                        @if ($notification->is_pinned)
                                                            <span
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                                                                Pinned
                                                            </span>
                                                        @endif
                                                    </h4>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </span>
                                                </div>

                                                <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>

                                                @if ($notification->getActionUrl())
                                                    <a href="{{ $notification->getActionUrl() }}"
                                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 mt-2">
                                                        View Details
                                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Rest of your existing notification sections --}}

                        {{-- Regular Notifications List --}}
                        @if ($regularNotifications->count() > 0)
                            <div class="regular-notifications">
                                @foreach ($regularNotifications as $notification)
                                    @include('notifications.partials.item', [
                                        'notification' => $notification,
                                        'unreadNotificationIds' => $unreadNotificationIds,
                                    ])
                                @endforeach
                            </div>

                            {{-- Pagination (hidden during filtering) --}}
                            <div class="mt-8" id="paginationContainer">
                                {{ $regularNotifications->links() }}
                            </div>
                        @endif

                        {{-- No Results Message (initially hidden) --}}
                        <div id="noResultsMessage" class="text-center py-12 hidden">
                            <div class="text-gray-500 dark:text-gray-400">
                                <x-icons.notification class="h-12 w-12 mx-auto mb-4" />
                                <h3 class="text-lg font-medium mb-2">Tidak ada notifikasi</h3>
                                <p>Tidak ada notifikasi yang sesuai dengan kategori yang dipilih.</p>
                            </div>
                        </div>
                    @else
                        @include('notifications.partials.empty-state')
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript for Dynamic Filtering --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoryButtons = document.querySelectorAll('.category-filter');
            const selectedCategoryElement = document.getElementById('selectedCategory');
            const notificationItems = document.querySelectorAll('.notification-item');
            const paginationContainer = document.getElementById('paginationContainer');
            const noResultsMessage = document.getElementById('noResultsMessage');
            const pinnedNotifications = document.querySelector('.pinned-notifications');
            const regularNotifications = document.querySelector('.regular-notifications');

            // Add data attributes to notification items based on their type and category
            notificationItems.forEach(item => {
                const notificationData = getNotificationDataFromElement(item);
                item.setAttribute('data-notification-type', notificationData.type || 'unknown');
                item.setAttribute('data-notification-category', notificationData.category || 'regular');
            });

            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const selectedCategory = this.getAttribute('data-category');

                    // Update selected category display
                    selectedCategoryElement.textContent = selectedCategory;

                    // Update active state
                    categoryButtons.forEach(btn => {
                        btn.classList.remove('bg-gray-100', 'dark:bg-gray-800');
                    });
                    this.classList.add('bg-gray-100', 'dark:bg-gray-800');

                    // Filter notifications
                    filterNotifications(selectedCategory);

                    // Close dropdown (if using Tailwind's default behavior)
                    document.body.click();
                });
            });

            function getNotificationDataFromElement(element) {
                // Try to extract notification type and category from the element's content or data attributes
                const messageText = element.querySelector('p')?.textContent?.toLowerCase() || '';

                // Determine category first
                let category = 'regular';

                // Check for system notifications (badges, announcements, onboarding)
                if (messageText.includes('badge') || messageText.includes('lencana') ||
                    messageText.includes('pengumuman') || messageText.includes('announcement') ||
                    messageText.includes('selamat datang') || messageText.includes('welcome') ||
                    element.querySelector('.ring-2')) {
                    category = 'system';
                }

                // Determine type
                let type = 'unknown';
                if (messageText.includes('menjawab pertanyaan') || messageText.includes('answered your post')) {
                    type = 'post_answered';
                } else if (messageText.includes('mengikuti') || messageText.includes('followed you')) {
                    type = 'user_followed';
                } else if (messageText.includes('memposting') || messageText.includes('posted')) {
                    type = 'followed_user_posted';
                } else if (messageText.includes('badge') || messageText.includes('lencana')) {
                    type = 'badge_earned';
                } else if (messageText.includes('pengumuman') || messageText.includes('announcement')) {
                    type = 'announcement';
                } else if (messageText.includes('selamat datang') || messageText.includes('welcome')) {
                    type = 'onboarding';
                }

                return {
                    type: type,
                    category: category
                };
            }

            function filterNotifications(category) {
                let visibleCount = 0;
                let visiblePinnedCount = 0;
                let visibleRegularCount = 0;

                notificationItems.forEach(item => {
                    const notificationType = item.getAttribute('data-notification-type');
                    const notificationCategory = item.getAttribute('data-notification-category');
                    let shouldShow = false;

                    switch (category) {
                        case 'Semua':
                            shouldShow = true;
                            break;
                        case 'Pertanyaan / Diskusi Saya':
                            shouldShow = notificationType === 'post_answered';
                            break;
                        case 'Pertanyaan / Diskusi yang Diikuti':
                            shouldShow = notificationType === 'followed_user_posted';
                            break;
                        case 'Lainnya':
                            // Lainnya includes everything except specific post/discussion types
                            shouldShow = !['post_answered', 'followed_user_posted'].includes(
                                notificationType);
                            break;
                    }

                    if (shouldShow) {
                        item.style.display = 'block';
                        item.style.opacity = '1';
                        visibleCount++;

                        // Count visible items in each section
                        if (item.closest('.pinned-notifications')) {
                            visiblePinnedCount++;
                        } else if (item.closest('.regular-notifications')) {
                            visibleRegularCount++;
                        }
                    } else {
                        item.style.display = 'none';
                        item.style.opacity = '0';
                    }
                });

                // Show/hide sections based on visible items
                if (pinnedNotifications) {
                    pinnedNotifications.style.display = visiblePinnedCount > 0 ? 'block' : 'none';
                }

                if (regularNotifications) {
                    regularNotifications.style.display = visibleRegularCount > 0 ? 'block' : 'none';
                }

                // Show/hide pagination and no results message
                if (category === 'Semua') {
                    paginationContainer.style.display = 'block';
                    noResultsMessage.classList.add('hidden');
                } else {
                    paginationContainer.style.display = 'none';

                    if (visibleCount === 0) {
                        noResultsMessage.classList.remove('hidden');
                    } else {
                        noResultsMessage.classList.add('hidden');
                    }
                }
            }
        });
    </script>
</x-app-layout>
