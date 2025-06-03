{{-- resources/views/components/toast.blade.php --}}
@props([
    'type' => 'success', // success, error, info
    'message' => '',
    'duration' => 4000, // milliseconds, 0 for persistent
    'dismissible' => true,
    'position' => 'top-right', // top-right, top-left, bottom-right, bottom-left, top-center, bottom-center
    'id' => null,
])

@php
    $toastId = $id ?? 'toast-' . uniqid();

    // Define colors based on type
    $colors = [
        'success' => [
            'bg' => 'bg-essentials-success',
            'text' => 'text-white',
            'icon' => 'text-white',
        ],
        'error' => [
            'bg' => 'bg-essentials-alert',
            'text' => 'text-white',
            'icon' => 'text-white',
        ],
        'info' => [
            'bg' => 'bg-blue-600',
            'text' => 'text-white',
            'icon' => 'text-white',
        ],
    ];

    $colorClasses = $colors[$type] ?? $colors['info'];

    // Define position classes with mobile responsiveness
    $positions = [
        'top-right' => 'top-4 right-4 sm:max-w-sm sm:w-auto max-w-none w-full left-4 right-4 sm:left-auto',
        'top-left' => 'top-4 left-4 sm:max-w-sm sm:w-auto max-w-none w-full left-4 right-4 sm:right-auto',
        'bottom-right' => 'bottom-4 right-4 sm:max-w-sm sm:w-auto max-w-none w-full left-4 right-4 sm:left-auto',
        'bottom-left' => 'bottom-4 left-4 sm:max-w-sm sm:w-auto max-w-none w-full left-4 right-4 sm:right-auto',
        'top-center' =>
            'top-4 left-4 right-4 sm:left-1/2 sm:right-auto sm:transform sm:-translate-x-1/2 sm:max-w-sm sm:w-auto',
        'bottom-center' =>
            'bottom-4 left-4 right-4 sm:left-1/2 sm:right-auto sm:transform sm:-translate-x-1/2 sm:max-w-sm sm:w-auto',
    ];

    $positionClasses = $positions[$position] ?? $positions['top-right'];
@endphp

<div id="{{ $toastId }}"
    class="fixed {{ $positionClasses }} z-50 mx-auto transform transition-all duration-300 ease-in-out opacity-0 translate-y-2 pointer-events-none"
    style="display: none;">
    <div
        class="{{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} px-4 py-3 rounded-lg shadow-lg border-l-4 border-white border-opacity-20">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <!-- Icon based on type -->
                <div class="flex-shrink-0">
                    @if ($type === 'success')
                        <svg class="w-5 h-5 {{ $colorClasses['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    @elseif($type === 'error')
                        <svg class="w-5 h-5 {{ $colorClasses['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                    @else
                        {{-- Better info icon --}}
                        <svg class="w-5 h-5 {{ $colorClasses['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                                clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </div>

                <!-- Message -->
                <p class="text-sm font-medium flex-1" id="{{ $toastId }}-message">{{ $message }}</p>
            </div>

            <!-- Close button -->
            @if ($dismissible)
                <button type="button"
                    class="ml-3 flex-shrink-0 {{ $colorClasses['text'] }} hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-25 rounded-full p-1 transition-opacity duration-150"
                    onclick="ToastManager.hide('{{ $toastId }}')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Progress bar for auto-dismiss -->
        @if ($duration > 0)
            <div class="mt-2 h-1 bg-white bg-opacity-20 rounded-full overflow-hidden">
                <div id="{{ $toastId }}-progress"
                    class="h-full bg-white bg-opacity-40 rounded-full transition-all ease-linear"
                    style="width: 100%; animation: toast-progress {{ $duration }}ms linear forwards;"></div>
            </div>
        @endif
    </div>
</div>

{{-- Toast CSS Animation --}}
@once
    <style>
        @keyframes toast-progress {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }
    </style>
@endonce

{{-- Enhanced Toast Manager - Only load once --}}
@once
    <script>
        // Enhanced Toast Manager - Singleton Pattern
        window.ToastManager = (function() {
            let activeToasts = new Set();
            let toastQueue = [];
            let maxToasts = 5;

            return {
                show: function(id, message = null, duration = null) {
                    const toast = document.getElementById(id);
                    if (!toast) return;

                    // Update message if provided
                    if (message) {
                        const messageElement = document.getElementById(id + '-message');
                        if (messageElement) {
                            messageElement.textContent = message;
                        }
                    }

                    // Limit concurrent toasts
                    if (activeToasts.size >= maxToasts) {
                        // Hide oldest toast
                        const oldestToast = activeToasts.values().next().value;
                        this.hide(oldestToast);
                    }

                    // Show toast with animation
                    toast.style.display = 'block';
                    setTimeout(() => {
                        toast.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
                        toast.classList.add('opacity-100', 'translate-y-0');
                    }, 10);

                    activeToasts.add(id);

                    // Auto-hide if duration is specified
                    const toastDuration = duration || parseInt(toast.dataset.duration) || {{ $duration }};
                    if (toastDuration > 0) {
                        setTimeout(() => {
                            this.hide(id);
                        }, toastDuration);
                    }
                },

                hide: function(id) {
                    const toast = document.getElementById(id);
                    if (!toast) return;

                    // Hide with animation
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');

                    // Remove from active set
                    activeToasts.delete(id);

                    // Hide completely after animation
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 300);
                },

                create: function(message, type = 'info', options = {}) {
                    const defaults = {
                        duration: 4000,
                        position: 'top-right',
                        dismissible: true
                    };

                    const config = Object.assign(defaults, options);
                    const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

                    // Color configurations
                    const colors = {
                        success: {
                            bg: 'bg-essentials-success',
                            text: 'text-white',
                            icon: 'text-white'
                        },
                        error: {
                            bg: 'bg-essentials-alert',
                            text: 'text-white',
                            icon: 'text-white'
                        },
                        info: {
                            bg: 'bg-blue-600',
                            text: 'text-white',
                            icon: 'text-white'
                        }
                    };

                    // Mobile-responsive position classes
                    const positions = {
                        'top-right': 'top-4 right-4 sm:max-w-sm sm:w-auto max-w-none w-full left-4 right-4 sm:left-auto',
                        'top-left': 'top-4 left-4 sm:max-w-sm sm:w-auto max-w-none w-full left-4 right-4 sm:right-auto',
                        'bottom-right': 'bottom-4 right-4 sm:max-w-sm sm:w-auto max-w-none w-full left-4 right-4 sm:left-auto',
                        'bottom-left': 'bottom-4 left-4 sm:max-w-sm sm:w-auto max-w-none w-full left-4 right-4 sm:right-auto',
                        'top-center': 'top-4 left-4 right-4 sm:left-1/2 sm:right-auto sm:transform sm:-translate-x-1/2 sm:max-w-sm sm:w-auto',
                        'bottom-center': 'bottom-4 left-4 right-4 sm:left-1/2 sm:right-auto sm:transform sm:-translate-x-1/2 sm:max-w-sm sm:w-auto'
                    };

                    const colorClasses = colors[type] || colors.info;
                    const positionClasses = positions[config.position] || positions['top-right'];

                    // Updated icons with better info icon
                    const icons = {
                        success: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>',
                        error: '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>',
                        info: '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>'
                    };

                    const progressBar = config.duration > 0 ? `
                    <div class="mt-2 h-1 bg-white bg-opacity-20 rounded-full overflow-hidden">
                        <div id="${toastId}-progress" class="h-full bg-white bg-opacity-40 rounded-full transition-all ease-linear" style="width: 100%; animation: toast-progress ${config.duration}ms linear forwards;"></div>
                    </div>
                ` : '';

                    const closeButton = config.dismissible ? `
                    <button type="button" class="ml-3 flex-shrink-0 ${colorClasses.text} hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-25 rounded-full p-1 transition-opacity duration-150" onclick="ToastManager.hide('${toastId}')">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                ` : '';

                    const toastHTML = `
                    <div id="${toastId}" class="fixed ${positionClasses} z-50 mx-auto transform transition-all duration-300 ease-in-out opacity-0 translate-y-2 pointer-events-none" style="display: none;">
                        <div class="${colorClasses.bg} ${colorClasses.text} px-4 py-3 rounded-lg shadow-lg border-l-4 border-white border-opacity-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 ${colorClasses.icon}" fill="currentColor" viewBox="0 0 20 20">
                                            ${icons[type] || icons.info}
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium flex-1" id="${toastId}-message">${message}</p>
                                </div>
                                ${closeButton}
                            </div>
                            ${progressBar}
                        </div>
                    </div>
                `;

                    // Add to DOM
                    document.body.insertAdjacentHTML('beforeend', toastHTML);

                    // Show toast
                    this.show(toastId);

                    // Clean up after hiding
                    setTimeout(() => {
                        const element = document.getElementById(toastId);
                        if (element) {
                            element.remove();
                        }
                    }, (config.duration || 0) + 500);

                    return toastId;
                }
            };
        })();

        // Backward compatibility
        window.showToast = ToastManager.show;
        window.hideToast = ToastManager.hide;
        window.toast = ToastManager.create;

        // Initialize toast if message is provided
        document.addEventListener('DOMContentLoaded', function() {
            @if ($message)
                ToastManager.show('{{ $toastId }}');
            @endif
        });
    </script>
@endonce
