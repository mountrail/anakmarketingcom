{{-- resources/views/components/loading-spinner.blade.php --}}
@props([
    'size' => 'md', // sm, md, lg
    'color' => 'primary', // primary, white, inactive
])

@php
    $sizeClasses = [
        'sm' => 'w-4 h-4',
        'md' => 'w-5 h-5',
        'lg' => 'w-6 h-6',
    ];

    $colorClasses = [
        'primary' => 'text-branding-primary',
        'white' => 'text-white',
        'inactive' => 'text-essentials-inactive',
    ];

    $spinnerSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    $spinnerColor = $colorClasses[$color] ?? $colorClasses['primary'];
@endphp

<svg class="animate-spin  {{ $attributes }} {{ $spinnerSize }} {{ $spinnerColor }}" xmlns="http://www.w3.org/2000/svg"
    fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
    </path>
</svg>
