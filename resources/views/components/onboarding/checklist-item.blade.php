{{-- resources/views/components/onboarding/checklist-item.blade.php --}}
@props(['title', 'completed' => false, 'route' => null, 'onclick' => null])

@if ($completed)
    <div class="flex items-center justify-between bg-branding-primary rounded-lg p-4 text-white">
        <span class="font-medium">{{ $title }}</span>
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                clip-rule="evenodd"></path>
        </svg>
    </div>
@else
    <div class="border-2 border-branding-primary rounded-lg p-4 bg-white cursor-pointer hover:bg-gray-50 transition-colors"
        @if ($route) onclick="window.location.href='{{ $route }}'" @endif
        @if ($onclick) onclick="{{ $onclick }}" @endif>
        <span class="font-medium text-branding-primary">{{ $title }}</span>
    </div>
@endif
