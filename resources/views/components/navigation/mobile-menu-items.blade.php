{{-- resources/views/components/navigation/mobile-menu-items.blade.php --}}
<div>
    {{-- Navigation Links --}}
    <x-dropdown-link :href="route('home')" class="{{ request()->routeIs('home') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
        {{ __('Home') }}
    </x-dropdown-link>

    <x-dropdown-link href="https://insight.anakmarketing.com">
        {{ __('Insights') }}
    </x-dropdown-link>

    @auth
        <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>

        {{-- User Menu Items --}}
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

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                {{ __('Keluar') }}
            </x-dropdown-link>
        </form>
    @else
        <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
        <x-dropdown-link href="#"
            @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'login'}))">
            {{ __('Masuk / Daftar') }}
        </x-dropdown-link>
    @endauth
</div>
