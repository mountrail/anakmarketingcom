{{-- resources/views/notifications/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Header --}}
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            Pusat Notifikasi
                        </h1>

                        {{-- Category Filter Dropdown --}}
                        <div class="flex justify-center">
                            <x-dropdown align="center" width="64">
                                <x-slot name="trigger">
                                    <button
                                        class="flex items-center w-80 rounded-md font-medium px-5 py-2.5 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:border-secondary-pale focus:ring-secondary-pale shadow-md">
                                        <span class="text-lg"
                                            id="selected-category">{{ request('category', 'Semua') }}</span>
                                        <svg class="ms-auto h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="py-1 max-h-none overflow-visible">
                                        <a href="{{ route('notifications.index', ['category' => 'Semua']) }}"
                                            class="block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
                                            onclick="updateSelectedCategory('Semua')">
                                            Semua
                                        </a>
                                        <a href="{{ route('notifications.index', ['category' => 'Pertanyaan / Diskusi Saya']) }}"
                                            class="block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
                                            onclick="updateSelectedCategory('Pertanyaan / Diskusi Saya')">
                                            Pertanyaan / Diskusi Saya
                                        </a>
                                        <a href="{{ route('notifications.index', ['category' => 'Pertanyaan / Diskusi yang Diikuti']) }}"
                                            class="block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
                                            onclick="updateSelectedCategory('Pertanyaan / Diskusi yang Diikuti')">
                                            Pertanyaan / Diskusi yang Diikuti
                                        </a>
                                        <a href="{{ route('notifications.index', ['category' => 'Lainnya']) }}"
                                            class="block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
                                            onclick="updateSelectedCategory('Lainnya')">
                                            Lainnya
                                        </a>
                                    </div>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>

                    {{-- Mark All as Read Button --}}
                    @if (auth()->user()->unreadNotifications->count() > 0)
                        <div class="text-center mb-6">
                            <button onclick="markAllAsRead()"
                                class="inline-flex items-center px-4 py-2 bg-branding-primary text-white rounded-md hover:bg-opacity-90 transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Tandai Semua Sudah Dibaca
                            </button>
                        </div>
                    @endif

                    {{-- Notifications List --}}
                    @if ($notifications->count() > 0)
                        <div class="space-y-0">
                            @foreach ($notifications as $notification)
                                @php
                                    $isUnread = is_null($notification->read_at);
                                    $isPinned =
                                        isset($notification->data['is_pinned']) && $notification->data['is_pinned'];
                                    $isAnnouncement =
                                        isset($notification->data['type']) &&
                                        $notification->data['type'] === 'announcement';

                                    // Get avatar URL based on notification type
                                    $avatarUrl = null;
                                    if (isset($notification->data['follower_avatar'])) {
                                        $avatarUrl = $notification->data['follower_avatar'];
                                    } elseif (isset($notification->data['answerer_avatar'])) {
                                        $avatarUrl = $notification->data['answerer_avatar'];
                                    } elseif (isset($notification->data['poster_avatar'])) {
                                        $avatarUrl = $notification->data['poster_avatar'];
                                    } elseif ($isAnnouncement) {
                                        $avatarUrl = '/images/admin-avatar.png'; // Default admin avatar
                                    }

                                    // Get action URL
                                    $actionUrl = isset($notification->data['action_url'])
                                        ? $notification->data['action_url']
                                        : '#';
                                @endphp

                                <div class="notification-item {{ $isUnread ? 'unread' : 'read' }}"
                                    data-notification-id="{{ $notification->id }}">
                                    <a href="{{ $actionUrl }}" onclick="markAsRead('{{ $notification->id }}')"
                                        class="block">
                                        <div
                                            class="flex items-start space-x-4 p-4 border-b border-gray-200 dark:border-gray-700
                                            {{ $isPinned ? 'bg-orange-50 dark:bg-orange-900/20' : 'bg-white dark:bg-gray-800' }}
                                            hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 cursor-pointer">

                                            {{-- Avatar --}}
                                            <div class="flex-shrink-0">
                                                @if ($avatarUrl)
                                                    <img src="{{ $avatarUrl }}" alt="User Avatar"
                                                        class="w-12 h-12 rounded-full object-cover">
                                                @else
                                                    <div
                                                        class="w-12 h-12 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400"
                                                            fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Content --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <p
                                                            class="text-base {{ $isUnread ? 'font-bold' : 'font-normal' }} text-gray-900 dark:text-white leading-relaxed">
                                                            {{ $notification->data['message'] }}
                                                        </p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </p>
                                                    </div>

                                                    {{-- Unread indicator --}}
                                                    @if ($isUnread)
                                                        <div
                                                            class="w-3 h-3 bg-orange-500 rounded-full flex-shrink-0 ml-3">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-8">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-12">
                            <div
                                class="mx-auto w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                <x-icons.notification class="w-12 h-12 text-gray-400"></x-icons.notification>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Belum ada notifikasi
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">
                                Notifikasi akan muncul di sini ketika ada aktivitas terkait akun Anda
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
        // Update selected category text
        function updateSelectedCategory(category) {
            document.getElementById('selected-category').textContent = category;
        }

        // Mark single notification as read
        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notificationElement = document.querySelector(
                            `[data-notification-id="${notificationId}"]`);
                        if (notificationElement) {
                            // Update visual state
                            notificationElement.classList.remove('unread');
                            notificationElement.classList.add('read');

                            // Update font weight
                            const messageElement = notificationElement.querySelector('p.font-bold');
                            if (messageElement) {
                                messageElement.classList.remove('font-bold');
                                messageElement.classList.add('font-normal');
                            }

                            // Remove unread indicator
                            const indicator = notificationElement.querySelector('.w-3.h-3.bg-orange-500');
                            if (indicator) indicator.remove();
                        }

                        // Update navbar notification icons
                        updateNotificationIcons();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memperbarui notifikasi');
                });
        }

        // Mark all notifications as read
        function markAllAsRead() {
            fetch('/notifications/mark-all-read', {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to update all visual states
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memperbarui notifikasi');
                });
        }

        // Update notification icons in navbar (defined in navigation.blade.php)
        function updateNotificationIcons() {
            if (typeof window.updateNotificationIcons === 'function') {
                window.updateNotificationIcons();
            }
        }

        // Set initial category on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const category = urlParams.get('category') || 'Semua';
            updateSelectedCategory(category);
        });
    </script>
</x-app-layout>
