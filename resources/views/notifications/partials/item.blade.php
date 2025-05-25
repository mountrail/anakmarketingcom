{{-- resources/views/notifications/partials/item.blade.php --}}
@php
    $isUnread = is_null($notification->read_at);
    $isPinned = isset($notification->data['is_pinned']) && $notification->data['is_pinned'];
    $isAnnouncement = isset($notification->data['type']) && $notification->data['type'] === 'announcement';

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

    // Get action URL - this will mark as read and redirect
    $actionUrl = isset($notification->data['action_url'])
        ? route('notifications.read', ['id' => $notification->id, 'redirect' => $notification->data['action_url']])
        : route('notifications.read', ['id' => $notification->id]);
@endphp

<div class="notification-item {{ $isUnread ? 'unread' : 'read' }}">
    <a href="{{ $actionUrl }}" class="block">
        <div
            class="flex items-start space-x-4 p-4 border-b border-gray-200 dark:border-gray-700
            {{ $isPinned ? 'bg-orange-50 dark:bg-orange-900/20' : 'bg-white dark:bg-gray-800' }}
            hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 cursor-pointer">

            {{-- Avatar --}}
            <div class="flex-shrink-0">
                @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="User Avatar" class="w-12 h-12 rounded-full object-cover">
                @else
                    <div class="w-12 h-12 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
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
                        <div class="w-3 h-3 bg-orange-500 rounded-full flex-shrink-0 ml-3"></div>
                    @endif
                </div>
            </div>
        </div>
    </a>
</div>
