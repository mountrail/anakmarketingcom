<!-- resources/views/layouts/navigation.blade.php -->
<nav x-data="{ open: false, userDropdown: false }" class="bg-white dark:bg-gray-800 shadow-md fixed top-0 left-0 right-0 w-full z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <x-icons.application-logo
                            class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
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
                    <!-- Notification Button -->
                    <div class="relative mr-4">
                        <a href="{{ route('notifications.index') }}"
                            class="inline-flex items-center justify-center p-2 rounded-md {{ request()->routeIs('notifications.*') ? 'text-branding-primary bg-branding-primary/10' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }} dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200"
                            title="Notifications">

                            @php
                                $unreadCount = auth()->user()->unreadNotifications->count();
                                $isOnNotificationPage = request()->routeIs('notifications.*');
                            @endphp

                            @if ($isOnNotificationPage)
                                <!-- Active state when on notification page -->
                                <x-icons.notification-selected class="h-5 w-5" />
                            @elseif ($unreadCount > 0)
                                <!-- Has unread notifications -->
                                <x-icons.notification-selected class="h-5 w-5" />
                                <!-- Notification Badge -->
                                <span
                                    class="notification-badge absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px] h-[18px]">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @else
                                <!-- No unread notifications -->
                                <x-icons.notification class="h-5 w-5" />
                            @endif
                        </a>
                    </div>

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
                            <!-- Notification Link for Mobile -->
                            <x-dropdown-link :href="route('notifications.index')"
                                class="flex items-center justify-between {{ request()->routeIs('notifications.*') ? 'bg-gray-100 dark:bg-gray-700 text-branding-primary' : '' }}">
                                <span class="flex items-center">
                                    @php
                                        $unreadCount = auth()->user()->unreadNotifications->count();
                                        $isOnNotificationPage = request()->routeIs('notifications.*');
                                    @endphp

                                    @if ($isOnNotificationPage)
                                        <x-icons.notification-selected class="h-4 w-4 mr-2" />
                                    @elseif ($unreadCount > 0)
                                        <x-icons.notification-selected class="h-4 w-4 mr-2" />
                                    @else
                                        <x-icons.notification class="h-4 w-4 mr-2" />
                                    @endif
                                    {{ __('Notifikasi') }}
                                </span>
                                @if ($unreadCount > 0 && !$isOnNotificationPage)
                                    <span
                                        class="notification-badge inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px] h-[18px]">
                                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                    </span>
                                @endif
                            </x-dropdown-link>

                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>

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

    // Update notification count dynamically (called from notification page)
    function updateNotificationCount() {
        fetch('/notifications/unread-count', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update all notification badges
                const badges = document.querySelectorAll('.notification-badge');
                badges.forEach(badge => {
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badge.style.display = 'inline-flex';
                    } else {
                        badge.style.display = 'none';
                    }
                });

                // Update notification icons if not on notification page
                if (!window.location.pathname.includes('/notifications')) {
                    const notificationLinks = document.querySelectorAll('a[href*="/notifications"]');
                    notificationLinks.forEach(link => {
                        const icon = link.querySelector('svg');
                        if (icon) {
                            // Update icon based on unread count
                            if (data.unread_count > 0) {
                                // Switch to selected icon
                                if (icon.classList.contains('notification-icon')) {
                                    icon.innerHTML =
                                        `<path d="M17.5124 14.2063L15.7124 12.3875V7.925C15.7687 4.49375 13.2749 1.56875 9.89991 1.04375C6.20616 0.556252 2.83116 3.1625 2.34366 6.8375C2.30616 7.11875 2.28741 7.41875 2.28741 7.7V12.3688L0.487408 14.1875C-0.150092 14.825 -0.131342 15.875 0.506158 16.5125C0.806158 16.8125 1.21866 16.9813 1.64991 16.9813H5.00616V17.3188C5.09991 19.4375 6.89991 21.0688 8.99991 20.975C11.1187 21.0688 12.8999 19.4375 12.9937 17.3188V16.9813H16.3499C17.2499 16.9813 17.9812 16.2313 17.9812 15.3313C17.9812 14.9 17.8124 14.4875 17.5124 14.1875V14.2063ZM11.0062 17.3375C10.8937 18.3313 10.0124 19.0625 8.99991 19.0063C7.98741 19.0813 7.10616 18.35 6.99366 17.3375V17H10.9874V17.3375H11.0062Z" fill="#FA9332"/>`;
                                }
                            } else {
                                // Switch to regular icon
                                if (icon.classList.contains('notification-icon')) {
                                    icon.innerHTML =
                                        `<path d="M17.5201 13.21L15.7201 11.4V6.94002C15.744 5.28384 15.1679 3.67493 14.0982 2.41035C13.0285 1.14578 11.5373 0.310951 9.90007 0.0600163C8.94989 -0.0651165 7.98394 0.0138312 7.06668 0.291592C6.14942 0.569352 5.30194 1.03954 4.5808 1.67078C3.85965 2.30202 3.28142 3.07979 2.88468 3.95221C2.48794 4.82463 2.28182 5.77163 2.28007 6.73002V11.4L0.480073 13.21C0.253979 13.4399 0.100657 13.7313 0.0392733 14.0478C-0.0221099 14.3643 0.0111694 14.6919 0.134951 14.9897C0.258732 15.2874 0.467524 15.542 0.735224 15.7217C1.00292 15.9014 1.31766 15.9982 1.64007 16H5.00007V16.34C5.04678 17.3552 5.49403 18.3105 6.2438 18.9965C6.99357 19.6826 7.98473 20.0434 9.00007 20C10.0154 20.0434 11.0066 19.6826 11.7563 18.9965C12.5061 18.3105 12.9534 17.3552 13.0001 16.34V16H16.3601C16.6825 15.9982 16.9972 15.9014 17.2649 15.7217C17.5326 15.542 17.7414 15.2874 17.8652 14.9897C17.989 14.6919 18.0223 14.3643 17.9609 14.0478C17.8995 13.7313 17.7462 13.4399 17.5201 13.21ZM11.0001 16.34C10.9446 16.821 10.7057 17.2621 10.3331 17.5713C9.96057 17.8805 9.48306 18.0341 9.00007 18C8.51709 18.0341 8.03957 17.8805 7.667 17.5713C7.29443 17.2621 7.05553 16.821 7.00007 16.34V16H11.0001V16.34ZM2.51007 14L3.69007 12.82C3.87724 12.634 4.02574 12.4127 4.127 12.169C4.22826 11.9253 4.28028 11.6639 4.28007 11.4V6.73002C4.28062 6.05542 4.42546 5.38874 4.70487 4.77473C4.98428 4.16071 5.3918 3.61357 5.90007 3.17002C6.40149 2.7157 6.99565 2.37577 7.64141 2.17375C8.28718 1.97173 8.96914 1.91245 9.64007 2.00002C10.7966 2.18779 11.8463 2.78703 12.596 3.6874C13.3457 4.58776 13.7449 5.72865 13.7201 6.90002V11.4C13.7186 11.6632 13.769 11.9242 13.8685 12.1678C13.9681 12.4115 14.1147 12.6331 14.3001 12.82L15.4901 14H2.51007Z" fill="#4D4D4D"/>`;
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error updating notification count:', error);
            });
    }
</script>
