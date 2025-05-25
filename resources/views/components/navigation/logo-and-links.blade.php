{{-- resources/views/components/navigation/logo-and-links.blade.php --}}
<div class="flex items-center">
    {{-- Logo --}}
    <div class="shrink-0 flex items-center">
        <a href="{{ route('home') }}" class="flex items-center">
            <x-icons.application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
            <span class="sr-only">Go to Anak Marketing homepage</span>
        </a>
    </div>

    {{-- Navigation Links --}}
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
