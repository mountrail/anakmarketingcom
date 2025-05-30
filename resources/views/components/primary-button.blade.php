{{-- resources/views/components/primary-button.blade.php --}}
@props([
    'variant' => 'primary', // primary, secondary, dark, inactive
    'size' => 'md', // sm, md, lg
    'disabled' => false,
    'type' => 'button',
])

@php
    $baseClasses =
        'inline-flex items-center justify-center font-medium rounded-md shadow-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';

    // Size variations
    $sizeClasses = [
        'sm' => 'px-3 py-1 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        'xl' => 'px-8 py-2 text-sm',
    ];

    // Variant styles
    $variantClasses = [
        'primary' => 'bg-branding-primary text-white hover:bg-opacity-90 focus:ring-blue-500',
        'secondary' => 'bg-white text-branding-black border border-gray-300 hover:bg-gray-50 focus:ring-blue-500',
        'dark' => 'bg-branding-dark text-branding-light hover:bg-opacity-90 focus:ring-gray-500',
        'inactive' => 'bg-essentials-inactive text-white hover:bg-opacity-90 focus:ring-gray-500',
    ];

    // Override with disabled styles if disabled
    if ($disabled) {
        $variantClasses = [
            'primary' => 'bg-essentials-inactive text-white cursor-not-allowed focus:ring-blue-500',
            'secondary' => 'bg-essentials-inactive text-white cursor-not-allowed focus:ring-blue-500',
            'dark' => 'bg-essentials-inactive text-white cursor-not-allowed focus:ring-gray-500',
            'inactive' => 'bg-essentials-inactive text-white cursor-not-allowed focus:ring-gray-500',
        ];
    }

    $classes = $baseClasses . ' ' . $sizeClasses[$size] . ' ' . $variantClasses[$variant];
@endphp

<button
    {{ $attributes->merge([
        'type' => $type,
        'class' => $classes,
        'disabled' => $disabled,
    ]) }}>
    {{ $slot }}
</button>
