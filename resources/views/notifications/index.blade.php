{{-- resources/views/notifications/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Header Partial --}}
                    @include('notifications.partials.header', ['category' => $category])

                    {{-- Mark All as Read Button Partial --}}
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
</x-app-layout>
