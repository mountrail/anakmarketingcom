{{-- resources/views/layouts/navigation.blade.php - Main Navigation --}}
<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 shadow-md fixed top-0 left-0 right-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex justify-between h-16">
            {{-- Logo and Primary Navigation --}}
            <x-navigation.logo-and-links />

            {{-- Desktop User Menu or Auth Buttons --}}
            <div class="hidden sm:flex sm:items-center">
                @auth
                    <x-navigation.desktop-user-menu />
                @else
                    <x-navigation.auth-buttons />
                @endauth
            </div>

            {{-- Mobile Menu --}}
            <x-navigation.mobile-menu />
        </div>
    </div>

    {{-- Mobile Bottom Auth Bar (Guests Only) --}}
    @guest
        <x-navigation.mobile-auth-bar />
    @endguest
</nav>

{{-- Content Padding --}}
<div class="pt-16">
    <!-- Your page content goes here -->
</div>

{{-- Auth Modal --}}
<x-auth-modal />

{{-- Navigation Scripts --}}
<x-navigation.scripts />
