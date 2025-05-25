{{-- resources/views/components/navigation/notification-button.blade.php --}}
@php
    $hasUnread = auth()->user()->unreadNotifications->count() > 0;
    $isOnNotificationPage = request()->routeIs('notifications.*');
@endphp

<div class="relative">
    <a href="{{ route('notifications.index') }}"
        class="relative inline-flex items-center justify-center p-2 rounded-md transition-colors duration-200 {{ $isOnNotificationPage ? 'text-branding-primary bg-branding-primary/10' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700' }}"
        title="Notifications">

        {{-- Notification Icon --}}
        @if ($isOnNotificationPage)
            <x-icons.notification-selected class="h-6 w-6" />
        @elseif ($hasUnread)
            <x-icons.notification-new class="h-6 w-6" />
        @else
            <x-icons.notification class="h-6 w-6" />
        @endif
    </a>
</div>
