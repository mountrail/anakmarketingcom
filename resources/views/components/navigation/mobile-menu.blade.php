{{-- resources/views/components/navigation/mobile-menu.blade.php --}}
<div class="flex items-center sm:hidden">
    @auth
        {{-- Mobile Notification Button --}}
        <div class="mr-2">
            <x-navigation.notification-button />
        </div>
    @endauth

    {{-- Hamburger Menu --}}
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <x-navigation.mobile-menu-items />
        </x-slot>
    </x-dropdown>
</div>
