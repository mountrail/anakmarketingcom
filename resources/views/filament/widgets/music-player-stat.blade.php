{{-- resources/views/filament/widgets/music-player-stat.blade.php --}}
<div @class([
    'fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10',
    'fi-color-amber',
])>
    {{-- Header --}}
    <div class="flex items-center gap-x-2 mb-4">
        <x-filament::icon icon="heroicon-m-musical-note"
            class="fi-wi-stats-overview-stat-icon h-5 w-5 text-amber-500 dark:text-amber-400" />

        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
            🎵 Music Player
        </h3>
    </div>

    {{-- Music Player Component --}}
    <div class="w-full">
        <livewire:music-player />
    </div>

    {{-- Description --}}
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
        Control your background music while working
    </p>
</div>
