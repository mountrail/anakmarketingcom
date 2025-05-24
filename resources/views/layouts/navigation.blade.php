<!-- resources/views/layouts/navigation.blade.php -->
<nav x-data="{ open: false, userDropdown: false }" class="bg-white dark:bg-gray-800 shadow-md fixed top-0 left-0 right-0 w-full z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <x-icons.application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                        <span class="sr-only">Go to Anak Marketing homepage</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 sm:ml-10 sm:flex">
                    <a href="{{ route('home') }}"
                        class="bg-branding-primary text-branding-light px-4 py-2 rounded-md text-sm font-medium shadow-md hover:bg-opacity-90 transition-colors">
                        {{ __('Home') }}
                    </a>
                    <a href="https://anakmarketing.com"
                        class="text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                        {{ __('Insights') }}
                    </a>
                </div>
            </div>

            @auth
                <!-- Settings Dropdown (For Authenticated Users) -->
                <div class="hidden sm:flex sm:items-center sm:ms-6 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 bg-branding-primary text-branding-light rounded-md text-sm font-medium shadow-md hover:bg-opacity-90 transition-colors">
                                <div>Hai, {{ Str::words(Auth::user()->name, 1, '') }}!</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.show', Auth::user())">
                                {{ __('Profil Saya') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('account.edit')">
                                {{ __('Pusat Akun') }}
                            </x-dropdown-link>

                            @if (Auth::user()->hasRole(['admin', 'editor']))
                                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                <x-dropdown-link href="{{ url('/admin') }}" target="_blank">
                                    {{ __('Admin Panel') }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Keluar') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @else
                <!-- Login/Register Links (For Guests) -->
                <div class="hidden sm:flex sm:items-center sm:space-x-4">
                    <x-primary-button variant="dark" size="md"
                        @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'register'}))">
                        Sign Up
                    </x-primary-button>
                    <x-primary-button size="md"
                        @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'login'}))">
                        Login
                    </x-primary-button>
                </div>
            @endauth

            <!-- Hamburger (with Dropdown) -->
            <div class="flex items-center sm:hidden relative">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Mobile Navigation Links -->
                        <x-dropdown-link :href="route('home')"
                            class="{{ request()->routeIs('home') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                            {{ __('Home') }}
                        </x-dropdown-link>

                        <x-dropdown-link href="https://anakmarketing.com">
                            {{ __('Insights') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>

                        @auth
                            <!-- User Menu Items -->
                            <x-dropdown-link :href="route('profile.show', Auth::user())">
                                {{ __('Profil Saya') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('account.edit')">
                                {{ __('Pusat Akun') }}
                            </x-dropdown-link>

                            <x-dropdown-link href="{{ route('posts.create') }}">
                                {{ __('Mulai Pertanyaan / Diskusi') }}
                            </x-dropdown-link>

                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                            @if (Auth::user()->hasRole(['admin', 'editor']))
                                <x-dropdown-link href="{{ url('/admin') }}" target="_blank">
                                    {{ __('Admin Panel') }}
                                </x-dropdown-link>
                            @endif
                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Keluar') }}
                                </x-dropdown-link>
                            </form>
                        @else
                            <!-- Auth Actions for Mobile -->
                            <x-dropdown-link href="#"
                                @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'login'}))">
                                {{ __('Masuk / Daftar') }}
                            </x-dropdown-link>
                        @endauth
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>

    @guest
        <!-- Mobile Bottom Navigation - Updated with primary buttons -->
        <div
            class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 py-2 sm:hidden z-30">

            <div class="flex justify-between items-center space-x-4">
                <!-- Sign Up Button (40%) -->
                <x-primary-button variant="dark" size="md" class="w-2/5"
                    @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'register'}))">
                    Daftar
                </x-primary-button>

                <!-- Login Button (60%) -->
                <x-primary-button size="md" class="w-3/5"
                    @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'login'}))">
                    Masuk
                </x-primary-button>
            </div>
        </div>
    @endguest
</nav>

<!-- Add padding to the body element to prevent content from being hidden under the navbar -->
<div class="pt-16">
    <!-- Your page content goes here -->
</div>

<!-- Auth Modal - Single Instance for whole layout -->
<x-auth-modal />

<script>
    // Initialize event handlers for auth modal triggers
    document.addEventListener('DOMContentLoaded', function() {
        // Handle data-auth-action attributes
        document.querySelectorAll('[data-auth-action]').forEach(function(element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.getAttribute('data-auth-action');
                window.dispatchEvent(new CustomEvent('open-auth-modal', {
                    detail: action
                }));
            });
        });
    });
</script>
