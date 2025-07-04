@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white dark:bg-gray-700'])

@php
    $alignmentClasses = match ($align) {
        'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
        'top' => 'origin-top',
        'center' => 'origin-top left-1/2 transform -translate-x-1/2',
        default => 'ltr:origin-top-right rtl:origin-top-left end-0',
    };

    $width = match ($width) {
        '48' => 'w-48',
        'auto' => 'w-auto',
        'trigger' => 'min-w-full w-max', // Changed: allow content to expand beyond trigger width
        default => $width,
    };
@endphp

<div class="relative" x-data="{
    open: false,
    init() {
        if ('{{ $width }}' === 'trigger') {
            this.$nextTick(() => {
                const trigger = this.$el.querySelector('[data-dropdown-trigger]');
                const content = this.$el.querySelector('[data-dropdown-content]');
                if (trigger && content) {
                    const triggerWidth = trigger.offsetWidth;
                    // Set minimum width to trigger width, but allow content to expand
                    content.style.minWidth = triggerWidth + 'px';
                    content.style.width = 'max-content';
                }
            });
        }
    }
}" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open" data-dropdown-trigger>
        {{ $trigger }}
    </div>

    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
        style="display: none;" data-dropdown-content @click="open = false">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
