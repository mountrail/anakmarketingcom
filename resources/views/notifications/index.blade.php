{{-- resources/views/notifications/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Notifikasi
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                                Kelola notifikasi dan update dari aktivitas Anda
                            </p>
                        </div>

                        @if (auth()->user()->unreadNotifications->count() > 0)
                            <button onclick="markAllAsRead()"
                                class="inline-flex items-center px-4 py-2 bg-branding-primary text-white rounded-md hover:bg-opacity-90 transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Tandai Semua Sudah Dibaca
                            </button>
                        @endif
                    </div>

                    {{-- Notifications Count --}}
                    @if ($notifications->total() > 0)
                        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                            Menampilkan {{ $notifications->count() }} dari {{ $notifications->total() }} notifikasi
                            @if (auth()->user()->unreadNotifications->count() > 0)
                                ({{ auth()->user()->unreadNotifications->count() }} belum dibaca)
                            @endif
                        </div>
                    @endif

                    {{-- Notifications List --}}
                    @if ($notifications->count() > 0)
                        <div class="space-y-2">
                            @foreach ($notifications as $notification)
                                <div class="notification-item {{ is_null($notification->read_at) ? 'unread' : 'read' }}"
                                    data-notification-id="{{ $notification->id }}">
                                    <div
                                        class="flex items-start space-x-4 p-4 rounded-lg border {{ is_null($notification->read_at) ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600' }} hover:shadow-md transition-all duration-200">

                                        {{-- Avatar --}}
                                        <div class="flex-shrink-0">
                                            @php
                                                $avatarUrl =
                                                    $notification->data['follower_avatar'] ??
                                                    ($notification->data['voter_avatar'] ??
                                                        ($notification->data['answerer_avatar'] ?? null));
                                            @endphp

                                            @if ($avatarUrl)
                                                <img src="{{ $avatarUrl }}" alt="User Avatar"
                                                    class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div
                                                    class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400"
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
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $notification->data['message'] }}
                                                </p>

                                                {{-- Unread indicator --}}
                                                @if (is_null($notification->read_at))
                                                    <div class="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></div>
                                                @endif
                                            </div>

                                            <div class="flex items-center justify-between mt-2">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </p>

                                                <div class="flex items-center space-x-2">
                                                    {{-- View Button --}}
                                                    @if (isset($notification->data['action_url']))
                                                        <a href="{{ $notification->data['action_url'] }}"
                                                            onclick="markAsRead('{{ $notification->id }}')"
                                                            class="inline-flex items-center px-3 py-1 bg-branding-primary text-white text-xs rounded-md hover:bg-opacity-90 transition-colors">
                                                            Lihat
                                                        </a>
                                                    @endif

                                                    {{-- Mark as read button --}}
                                                    @if (is_null($notification->read_at))
                                                        <button onclick="markAsRead('{{ $notification->id }}')"
                                                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                                            Tandai sudah dibaca
                                                        </button>
                                                    @endif

                                                    {{-- Delete button --}}
                                                    <button onclick="deleteNotification('{{ $notification->id }}')"
                                                        class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                <x-icons.notification class="w-12 h-12 text-gray-400" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Belum ada notifikasi
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">
                                Notifikasi akan muncul di sini ketika ada aktivitas terkait akun Anda
                            </p>
                            <a href="{{ route('home') }}"
                                class="inline-flex items-center px-4 py-2 bg-branding-primary text-white rounded-md hover:bg-opacity-90 transition-colors">
                                Kembali ke Beranda
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
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

                            // Update styling
                            const container = notificationElement.querySelector('.flex.items-start');
                            container.classList.remove('bg-blue-50', 'dark:bg-blue-900/20', 'border-blue-200',
                                'dark:border-blue-800');
                            container.classList.add('bg-gray-50', 'dark:bg-gray-700/50', 'border-gray-200',
                                'dark:border-gray-600');

                            // Remove unread indicator
                            const indicator = notificationElement.querySelector('.w-3.h-3.bg-blue-500');
                            if (indicator) indicator.remove();

                            // Remove "mark as read" button
                            const markButton = notificationElement.querySelector('button[onclick*="markAsRead"]');
                            if (markButton) markButton.remove();
                        }

                        // Update navbar notification count
                        updateNotificationCount();
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

        // Delete notification
        function deleteNotification(notificationId) {
            if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
                fetch(`/notifications/${notificationId}`, {
                        method: 'DELETE',
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
                                notificationElement.remove();
                            }

                            // Update navbar notification count
                            updateNotificationCount();

                            // Check if page is now empty
                            const remainingNotifications = document.querySelectorAll('[data-notification-id]');
                            if (remainingNotifications.length === 0) {
                                window.location.reload();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus notifikasi');
                    });
            }
        }

        // Update notification count in navbar
        function updateNotificationCount() {
            fetch('/notifications/unread-count', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Update badge in navbar if it exists
                    const badge = document.querySelector('.notification-badge');
                    if (badge) {
                        if (data.unread_count > 0) {
                            badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating notification count:', error);
                });
        }
    </script>
</x-app-layout>
