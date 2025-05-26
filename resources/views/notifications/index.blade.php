{{-- resources/views/notifications/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800">
                <div class="text-gray-900 dark:text-gray-100">

                    {{-- Header Partial --}}
                    @include('notifications.partials.header', ['category' => $category])

                    {{-- Action Buttons --}}
                    {{-- Mark All as Read Button --}}
                    @if (auth()->user()->unreadNotifications->count() > 0)
                        @include('notifications.partials.mark-all-read-button')
                    @endif

                    {{-- Notifications List Partial --}}
                    @if ($notifications->count() > 0)
                        @include('notifications.partials.list', ['notifications' => $notifications])

                        {{-- Pagination --}}
                        <div class="mt-8">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        @include('notifications.partials.empty-state')
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript for AJAX deletion --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle individual notification deletion
            document.addEventListener('click', function(e) {
                if (e.target.closest('.delete-notification-btn')) {
                    e.preventDefault();
                    e.stopPropagation();

                    const button = e.target.closest('.delete-notification-btn');
                    const notificationId = button.getAttribute('data-notification-id');
                    const notificationItem = button.closest('.notification-item');

                    deleteNotification(notificationId, notificationItem);
                }
            });

            // Function to delete individual notification
            function deleteNotification(notificationId, notificationElement) {
                fetch(`/notifications/${notificationId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Animate removal
                            notificationElement.style.opacity = '0';
                            notificationElement.style.transform = 'translateX(100%)';

                            setTimeout(() => {
                                notificationElement.remove();

                                // Check if there are no more notifications
                                const remainingNotifications = document.querySelectorAll(
                                    '.notification-item');
                                if (remainingNotifications.length === 0) {
                                    location.reload(); // Reload to show empty state
                                }
                            }, 300);
                        } else {
                            showNotification('Gagal menghapus notifikasi', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Terjadi kesalahan saat menghapus notifikasi', 'error');
                    });
            }

            // Function to show notification messages
            function showNotification(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full ${
                    type === 'success' ? 'bg-green-500 text-white' :
                    type === 'error' ? 'bg-red-500 text-white' :
                    'bg-blue-500 text-white'
                }`;
                notification.textContent = message;

                document.body.appendChild(notification);

                // Animate in
                setTimeout(() => {
                    notification.style.transform = 'translateX(0)';
                }, 100);

                // Remove after 3 seconds
                setTimeout(() => {
                    notification.style.transform = 'translateX(full)';
                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 300);
                }, 3000);
            }
        });
    </script>
</x-app-layout>
