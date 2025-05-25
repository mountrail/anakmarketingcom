{{-- resources/views/notifications/partials/header.blade.php --}}
<div class="text-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
        Pusat Notifikasi
    </h1>

    {{-- Category Filter Dropdown --}}
    <div class="flex justify-center">
        <x-dropdown align="center" width="64">
            <x-slot name="trigger">
                <button
                    class="flex items-center w-80 rounded-md font-medium px-5 py-2.5 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:border-secondary-pale focus:ring-secondary-pale shadow-md">
                    <span class="text-lg">{{ $category }}</span>
                    <svg class="ms-auto h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="py-1 max-h-none overflow-visible">
                    <a href="{{ route('notifications.index', ['category' => 'Semua']) }}"
                        class="block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out {{ $category === 'Semua' ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                        Semua
                    </a>
                    <a href="{{ route('notifications.index', ['category' => 'Pertanyaan / Diskusi Saya']) }}"
                        class="block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out {{ $category === 'Pertanyaan / Diskusi Saya' ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                        Pertanyaan / Diskusi Saya
                    </a>
                    <a href="{{ route('notifications.index', ['category' => 'Pertanyaan / Diskusi yang Diikuti']) }}"
                        class="block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out {{ $category === 'Pertanyaan / Diskusi yang Diikuti' ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                        Pertanyaan / Diskusi yang Diikuti
                    </a>
                    <a href="{{ route('notifications.index', ['category' => 'Lainnya']) }}"
                        class="block w-full px-5 py-3 text-lg font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out {{ $category === 'Lainnya' ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                        Lainnya
                    </a>
                </div>
            </x-slot>
        </x-dropdown>
    </div>
</div>
