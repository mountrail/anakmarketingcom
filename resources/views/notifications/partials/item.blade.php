{{-- resources/views/notifications/partials/item.blade.php --}}
@php
    // Check if this notification was unread when the page was loaded
    $wasUnread = in_array($notification->id, $unreadNotificationIds ?? []);
    $isPinned =
        isset($notification->data['is_pinned']) &&
        ($notification->data['is_pinned'] === true || $notification->data['is_pinned'] === 1);
    $isAnnouncement = isset($notification->data['type']) && $notification->data['type'] === 'announcement';
    $isSystemNotification =
        $isAnnouncement ||
        (isset($notification->data['type']) && in_array($notification->data['type'], ['system', 'badge_earned']));

    // Get avatar URL based on notification type
    $avatarUrl = null;
    if (isset($notification->data['follower_avatar'])) {
        $avatarUrl = $notification->data['follower_avatar'];
    } elseif (isset($notification->data['answerer_avatar'])) {
        $avatarUrl = $notification->data['answerer_avatar'];
    } elseif (isset($notification->data['poster_avatar'])) {
        $avatarUrl = $notification->data['poster_avatar'];
    }

    // Get action URL - this will mark as read and redirect
    $actionUrl = null;
    if (isset($notification->data['action_url'])) {
        $storedUrl = $notification->data['action_url'];

        // Convert relative URL to absolute URL for the redirect parameter
        $absoluteUrl = filter_var($storedUrl, FILTER_VALIDATE_URL) ? $storedUrl : url($storedUrl);

        $actionUrl = route('notifications.read', ['id' => $notification->id, 'redirect' => $absoluteUrl]);
    } else {
        $actionUrl = route('notifications.read', ['id' => $notification->id]);
    }
@endphp

<div class="notification-item {{ $wasUnread ? 'unread' : 'read' }} {{ $isPinned ? 'pinned' : '' }}"
    data-notification-id="{{ $notification->id }}">
    <div
        class="flex items-start space-x-4 p-4 border-b border-gray-200 dark:border-gray-700
        {{ $isPinned ? 'bg-branding-primary/10 hover:bg-branding-primary/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}
         transition-all duration-200">

        {{-- Main Content Area (Clickable) --}}
        <a href="{{ $actionUrl }}" class="flex items-start space-x-4 flex-1 cursor-pointer">
            {{-- Avatar --}}
            <div class="flex-shrink-0">
                @if ($isSystemNotification || !$avatarUrl)
                    {{-- Use AnakMarketing icon for system/admin notifications with ring --}}
                    <div
                        class="w-12 h-12 bg-white dark:bg-gray-600 rounded-full flex items-center justify-center border border-gray-200 dark:border-gray-600 ring-2 ring-branding-primary ring-opacity-50">
                        <x-icons.anakmarketing class="w-8 h-8" />
                    </div>
                @else
                    <img src="{{ $avatarUrl }}" alt="User Avatar"
                        class="w-12 h-12 rounded-full object-cover {{ $isPinned ? 'ring-2 ring-branding-primary ring-opacity-50' : '' }}">
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p
                            class="text-base {{ $wasUnread ? 'font-bold' : 'font-normal' }} text-gray-900 dark:text-white leading-relaxed">
                            {{ $notification->data['message'] }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>

                    {{-- Unread indicator - show if was unread when page loaded --}}
                    @if ($wasUnread)
                        <div class="w-3 h-3 bg-branding-primary rounded-full flex-shrink-0 ml-3"></div>
                    @endif
                </div>
            </div>
        </a>

    </div>
</div>
