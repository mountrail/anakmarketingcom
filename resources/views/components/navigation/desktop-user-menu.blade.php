{{-- resources/views/components/navigation/desktop-user-menu.blade.php --}}
<div class="flex items-center space-x-4">
    {{-- Notification Button --}}
    <x-navigation.notification-button />

    {{-- User Dropdown --}}
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button
                class="inline-flex items-center px-3 py-2 bg-branding-primary text-branding-light rounded-md text-sm font-bold shadow-md hover:bg-opacity-90 transition-colors">
                <div>Hai, {{ Str::words(Auth::user()->name, 1, '') }}!</div>
                <div class="ms-1">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </x-slot>

        <x-slot name="content">
            <x-navigation.user-menu-items />
        </x-slot>
    </x-dropdown>
</div>
