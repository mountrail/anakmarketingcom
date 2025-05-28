{{-- resources/views/profile/partials/badges-section.blade.php --}}
@props(['user', 'isOwner'])

@php
    use App\Services\BadgeService;

    if ($isOwner) {
        // Get all earned badges for selection
        $allUserBadges = BadgeService::getAllUserBadges($user);
        $displayedBadges = BadgeService::getDisplayedBadges($user);
        $selectedBadgeIds = $displayedBadges->pluck('badge_id')->toArray();
    } else {
        // Just get displayed badges for viewing
        $displayedBadges = BadgeService::getDisplayedBadges($user);
    }
@endphp

<div class="mb-20 space-y-6">
    <h2
        class="font-semibold text-branding-black dark:text-white text-center border-b border-gray-200 dark:border-gray-600 mb-4 pb-2">
        Badges
    </h2>

    @if ($isOwner)
        <p class="text-sm text-branding-black dark:text-gray-400 text-center mb-6">
            Pilih 3 badge untuk ditampilkan di profil Anda
        </p>

        @if ($allUserBadges->count() > 0)
            <form id="badge-form" action="{{ route('profile.update-badges') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8">
                    @foreach ($allUserBadges as $userProfileBadge)
                        @php
                            $badge = $userProfileBadge->badge;
                            $isSelected = in_array($badge->id, $selectedBadgeIds);
                        @endphp

                        <div class="flex flex-col items-center space-y-3">
                            <div class="relative">
                                <label for="badge_{{ $badge->id }}" class="cursor-pointer block">
                                    <div class="relative transition-transform duration-200 hover:scale-105">
                                        <x-icons.badge
                                            class="w-16 h-16 badge-icon {{ $isSelected ? 'text-yellow-500' : 'text-gray-400 dark:text-gray-600' }} transition-colors duration-200" />

                                        {{-- Checkbox positioned at top right --}}
                                        <div class="absolute -top-1 -right-1 w-5 h-5">
                                            <input type="checkbox" id="badge_{{ $badge->id }}" name="badges[]"
                                                value="{{ $badge->id }}"
                                                class="w-5 h-5 text-branding-primary bg-white border-2 border-gray-300 rounded focus:ring-branding-primary focus:ring-2 badge-checkbox"
                                                {{ $isSelected ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-semibold text-branding-black dark:text-white">{{ $badge->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $badge->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center">
                    <x-primary-button type="submit" size="xl" id="save-badges-btn"
                        class="disabled:bg-essentials-inactive disabled:opacity-100 disabled:cursor-not-allowed transition-all duration-200">
                        <span class="button-text">Simpan</span>
                        <span class="loading-spinner hidden">
                            <span class="inline-flex items-center">
                                <x-loading-spinner size="sm" color="white" />
                                <span class="ml-2">Menyimpan...</span>
                            </span>
                        </span>
                    </x-primary-button>
                </div>
            </form>
        @else
            <div class="text-center text-essentials-inactive dark:text-gray-400 py-8">
                <x-icons.badge class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
                <p class="text-lg font-medium mb-2">Belum ada badge yang didapatkan</p>
                <p class="text-sm">Mulai berpartisipasi untuk mendapatkan badge pertama Anda!</p>
            </div>
        @endif
    @else
        {{-- View mode for non-owners --}}
        @if ($displayedBadges->count() > 0)
            <div class="flex justify-center space-x-6">
                @foreach ($displayedBadges as $userProfileBadge)
                    @php $badge = $userProfileBadge->badge; @endphp
                    <div class="flex flex-col items-center space-y-3">
                        <x-icons.badge class="w-16 h-16 text-yellow-500" />
                        <div class="text-center">
                            <p class="text-sm font-semibold text-branding-black dark:text-white">{{ $badge->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $badge->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-essentials-inactive dark:text-gray-400 py-8">
                <x-icons.badge class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
                <p>Belum ada badge yang ditampilkan</p>
            </div>
        @endif
    @endif
</div>

@if ($isOwner)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.badge-checkbox');
            const saveButton = document.getElementById('save-badges-btn');
            const badgeForm = document.getElementById('badge-form');
            const maxSelection = 3;

            // Store original selected badges
            const originalSelectedBadges = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value)
                .sort();

            // Track changes to enable/disable button
            let badgesChanged = false;

            // Function to update button state and color
            function updateButtonState(button, hasChanges) {
                if (hasChanges) {
                    button.disabled = false;
                    // Remove disabled styles and add active brand-primary color
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                    button.classList.add('bg-branding-primary', 'hover:bg-opacity-90', 'focus:bg-opacity-90');
                } else {
                    button.disabled = true;
                    // Add disabled styles and remove active colors
                    button.classList.add('opacity-50', 'cursor-not-allowed');
                    button.classList.remove('bg-branding-primary', 'hover:bg-opacity-90', 'focus:bg-opacity-90');
                }
            }

            // Check for changes in badge selection
            function checkBadgeChanges() {
                const currentSelectedBadges = Array.from(checkboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value)
                    .sort();

                badgesChanged = JSON.stringify(currentSelectedBadges) !== JSON.stringify(originalSelectedBadges);
                updateButtonState(saveButton, badgesChanged);
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedBoxes = document.querySelectorAll('.badge-checkbox:checked');

                    // Update visual states
                    updateBadgeVisuals();

                    // Enforce max selection
                    if (checkedBoxes.length > maxSelection) {
                        this.checked = false;
                        updateBadgeVisuals();
                        alert('Anda hanya dapat memilih maksimal 3 badge.');
                        return;
                    }

                    // Check for changes to enable/disable button
                    checkBadgeChanges();
                });
            });

            function updateBadgeVisuals() {
                checkboxes.forEach(checkbox => {
                    const badgeContainer = checkbox.closest('.flex.flex-col');
                    const badgeIcon = badgeContainer.querySelector('.badge-icon');

                    if (checkbox.checked) {
                        // Selected state - make badge golden
                        badgeIcon.classList.remove('text-gray-400', 'dark:text-gray-600');
                        badgeIcon.classList.add('text-yellow-500');
                    } else {
                        // Unselected state - make badge gray
                        badgeIcon.classList.remove('text-yellow-500');
                        badgeIcon.classList.add('text-gray-400', 'dark:text-gray-600');
                    }
                });
            }

            // Show loading state on form submission
            function showLoadingState(button) {
                const buttonText = button.querySelector('.button-text');
                const loadingSpinner = button.querySelector('.loading-spinner');

                if (buttonText) buttonText.classList.add('hidden');
                if (loadingSpinner) {
                    loadingSpinner.classList.remove('hidden');
                    loadingSpinner.classList.add('inline-flex', 'items-center');
                }

                button.disabled = true;
            }

            // Add form submission handler for loading state
            if (badgeForm) {
                badgeForm.addEventListener('submit', function(e) {
                    if (badgesChanged) {
                        showLoadingState(saveButton);
                    } else {
                        e.preventDefault();
                    }
                });
            }

            // Initial state check
            checkBadgeChanges();
        });
    </script>
@endif
