{{-- resources/views/components/badge-preview.blade.php --}}
@props(['badges', 'user', 'maxVisible' => 3, 'badgeSize' => 'w-4 h-4'])

@php
    $visibleBadges = $badges->take($maxVisible);
    $remainingCount = $badges->count() - $maxVisible;

    // Determine thumbnail size based on badge size class
    $thumbnailSize = match (true) {
        str_contains($badgeSize, 'w-4 h-4') => '32x32',
        str_contains($badgeSize, 'w-7 h-7') => '56x56',
        str_contains($badgeSize, 'w-8 h-8') => '64x64',
        str_contains($badgeSize, 'w-12 h-12') => '96x96',
        default => '32x32',
    };
@endphp

<div class="flex items-center space-x-1">
    @if ($visibleBadges->count() > 0)
        @foreach ($visibleBadges as $userProfileBadge)
            <button type="button"
                onclick="showBadgeModal({{ json_encode($userProfileBadge->badge) }}, '{{ $user->name }}')"
                class="relative hover:scale-110 transition-transform duration-200 focus:outline-none focus:ring-2 focus:ring-branding-primary focus:ring-opacity-50 rounded-full">
                <img src="{{ asset('images/badges/thumbs/' . $thumbnailSize . '/' . $userProfileBadge->badge->icon) }}"
                    alt="{{ $userProfileBadge->badge->name }}" class="{{ $badgeSize }} object-contain"
                    onerror="this.onerror=null; this.src='{{ asset('images/badges/' . $userProfileBadge->badge->icon) }}'; this.classList.add('text-yellow-500');" />
            </button>
        @endforeach

        @if ($remainingCount > 0)
            <span class="text-xs text-gray-500 font-medium ml-1">
                +{{ $remainingCount }}
            </span>
        @endif
    @else
        <span class="text-xs text-gray-400 italic">
            Belum ada badge
        </span>
    @endif
</div>

@once
    @push('scripts')
        <script>
            // Badge modal functionality
            function showBadgeModal(badge, userName) {
                // Remove existing modal if any
                closeBadgeModal();

                // Create modal backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
                backdrop.id = 'badge-modal-backdrop';

                // Close modal when clicking backdrop
                backdrop.addEventListener('click', function(e) {
                    if (e.target === backdrop) {
                        closeBadgeModal();
                    }
                });

                // Create modal content
                const modal = document.createElement('div');
                modal.className =
                    'bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full mx-4 shadow-xl transform transition-all';
                modal.onclick = (e) => e.stopPropagation();

                modal.innerHTML = `
                <div class="text-center">
                    <div class="mb-4">
                        <img src="/images/badges/thumbs/96x96/${badge.icon}"
                            alt="${badge.name}"
                            class="w-32 h-32 object-contain mx-auto"
                            onerror="this.onerror=null; this.src='/images/badges/${badge.icon}';">
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        ${badge.name}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        ${badge.description}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mb-4">
                        Diraih oleh ${userName}
                    </p>
                    <button id="close-badge-modal"
                            class="px-4 py-2 bg-branding-primary hover:bg-branding-primary/90 text-white rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-branding-primary focus:ring-opacity-50">
                        Tutup
                    </button>
                </div>
            `;

                backdrop.appendChild(modal);
                document.body.appendChild(backdrop);
                document.body.style.overflow = 'hidden';

                // Add click event to close button
                const closeButton = modal.querySelector('#close-badge-modal');
                closeButton.addEventListener('click', closeBadgeModal);
            }

            function closeBadgeModal() {
                const modal = document.getElementById('badge-modal-backdrop');
                if (modal) {
                    document.body.removeChild(modal);
                    document.body.style.overflow = '';
                }
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeBadgeModal();
                }
            });
        </script>
    @endpush
@endonce
