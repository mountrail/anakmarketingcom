{{-- resources/views/profile/partials/badges-section.blade.php --}}
@props(['user'])

@php
    use App\Services\BadgeService;
    // Get all earned badges for selection (since edit page is owner-only)
    $allUserBadges = BadgeService::getAllUserBadges($user);
    $displayedBadges = BadgeService::getDisplayedBadges($user);
    $selectedBadgeIds = $displayedBadges->pluck('badge_id')->toArray();
@endphp

<div class="mb-20 space-y-6">
    <h2
        class="font-semibold text-branding-black dark:text-white text-center border-b border-gray-200 dark:border-gray-600 mb-4 pb-2">
        Badges
    </h2>

    <p class="text-sm text-branding-black dark:text-gray-400 text-center mb-6">
        Pilih 3 badge untuk ditampilkan di profil Anda (urutan berdasarkan waktu pemilihan)
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
                        $selectionOrder = $isSelected ? array_search($badge->id, $selectedBadgeIds) + 1 : 0;
                    @endphp

                    <div class="flex flex-col items-center space-y-3">
                        <div class="relative">
                            <label for="badge_{{ $badge->id }}" class="cursor-pointer block">
                                <div class="relative transition-transform duration-200 hover:scale-105">
                                    @if ($badge->icon && file_exists(public_path('images/badges/' . $badge->icon)))
                                        <img src="{{ asset('images/badges/' . $badge->icon) }}"
                                            alt="{{ $badge->name }}"
                                            class="w-32 h-32 object-contain badge-icon transition-opacity duration-200 {{ $isSelected ? 'opacity-100' : 'opacity-60' }}" />
                                    @else
                                        <x-icons.badge
                                            class="w-32 h-32 badge-icon {{ $isSelected ? 'text-yellow-500' : 'text-gray-400 dark:text-gray-600' }} transition-colors duration-200" />
                                    @endif

                                    {{-- Number indicator positioned at top right --}}
                                    <div class="absolute -top-1 -right-1 w-6 h-6">
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center text-xs font-bold badge-number {{ $isSelected ? 'bg-branding-primary text-white border-branding-primary' : 'text-gray-400' }} transition-all duration-200">
                                            <span
                                                class="number-display">{{ $selectionOrder > 0 ? $selectionOrder : '' }}</span>
                                        </div>
                                        <input type="checkbox" id="badge_{{ $badge->id }}" name="badges[]"
                                            value="{{ $badge->id }}" class="hidden badge-checkbox"
                                            data-selection-order="{{ $selectionOrder }}"
                                            {{ $isSelected ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-branding-black dark:text-white">
                                {{ $badge->name }}
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

</div>

{{-- Add tooltip for number clicking --}}
<style>
    .badge-number {
        cursor: pointer;
        transition: transform 0.1s ease;
    }

    .badge-number:hover {
        transform: scale(1.1);
    }

    .badge-number:active {
        transform: scale(0.95);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.badge-checkbox');
        const saveButton = document.getElementById('save-badges-btn');
        const badgeForm = document.getElementById('badge-form');
        const maxSelection = 3;
        let selectionOrder = [];

        // Store original selected badges WITH their order
        const originalOrder = Array.from(checkboxes)
            .filter(checkbox => checkbox.checked)
            .sort((a, b) => parseInt(a.dataset.selectionOrder) - parseInt(b.dataset.selectionOrder))
            .map(checkbox => checkbox.value);

        // Initialize selection order based on current selection
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const order = parseInt(checkbox.dataset.selectionOrder);
                if (order > 0) {
                    selectionOrder[order - 1] = checkbox.value;
                }
            }
        });

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

        // Check for changes in badge selection OR order
        function checkBadgeChanges() {
            // Get current order of selected badges
            const currentOrder = selectionOrder.filter(id => id !== undefined);

            // Compare both selection and order
            badgesChanged = JSON.stringify(currentOrder) !== JSON.stringify(originalOrder);
            updateButtonState(saveButton, badgesChanged);
        }

        // Update number displays based on selection order
        function updateNumberDisplays() {
            checkboxes.forEach(checkbox => {
                const badgeContainer = checkbox.closest('.flex.flex-col');
                const badgeIcon = badgeContainer.querySelector('.badge-icon');
                const numberContainer = badgeContainer.querySelector('.badge-number');
                const numberDisplay = numberContainer.querySelector('.number-display');

                const badgeId = checkbox.value;
                const orderIndex = selectionOrder.indexOf(badgeId);

                if (orderIndex !== -1) {
                    // Badge is selected - show number and golden color
                    const displayNumber = orderIndex + 1;
                    numberDisplay.textContent = displayNumber;

                    // Update number container styling
                    numberContainer.classList.remove('text-gray-400', 'border-gray-300', 'bg-white');
                    numberContainer.classList.add('bg-branding-primary', 'text-white',
                        'border-branding-primary');

                    // Update badge icon styling (handle both img and svg)
                    if (badgeIcon.tagName === 'IMG') {
                        badgeIcon.classList.remove('opacity-60');
                        badgeIcon.classList.add('opacity-100');
                    } else {
                        badgeIcon.classList.remove('text-gray-400', 'dark:text-gray-600');
                        badgeIcon.classList.add('text-yellow-500');
                    }

                    checkbox.checked = true;
                } else {
                    // Badge is not selected - hide number and gray color
                    numberDisplay.textContent = '';

                    // Update number container styling
                    numberContainer.classList.remove('bg-branding-primary', 'text-white',
                        'border-branding-primary');
                    numberContainer.classList.add('text-gray-400', 'border-gray-300', 'bg-white');

                    // Update badge icon color
                    if (badgeIcon.tagName === 'IMG') {
                        badgeIcon.classList.remove('opacity-100');
                        badgeIcon.classList.add('opacity-60');
                    } else {
                        badgeIcon.classList.remove('text-yellow-500');
                        badgeIcon.classList.add('text-gray-400', 'dark:text-gray-600');
                    }

                    checkbox.checked = false;
                }
            });
        }

        // Handle badge selection clicks
        checkboxes.forEach(checkbox => {
            const badgeContainer = checkbox.closest('.flex.flex-col');
            const label = badgeContainer.querySelector('label');

            label.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default label behavior

                const badgeId = checkbox.value;
                const orderIndex = selectionOrder.indexOf(badgeId);

                if (orderIndex !== -1) {
                    // Badge is currently selected - remove it
                    selectionOrder.splice(orderIndex, 1);
                } else {
                    // Badge is not selected - add it
                    if (selectionOrder.length >= maxSelection) {
                        alert('Anda hanya dapat memilih maksimal 3 badge.');
                        return;
                    }
                    selectionOrder.push(badgeId);
                }

                // Update displays and check for changes
                updateNumberDisplays();
                checkBadgeChanges();
            });
        });

        // Handle number clicks for reordering
        checkboxes.forEach(checkbox => {
            const badgeContainer = checkbox.closest('.flex.flex-col');
            const numberContainer = badgeContainer.querySelector('.badge-number');

            numberContainer.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const badgeId = checkbox.value;
                const currentIndex = selectionOrder.indexOf(badgeId);

                // Only allow reordering if badge is already selected
                if (currentIndex === -1) return;

                // Move to next position, or back to first if at end
                const newIndex = (currentIndex + 1) % selectionOrder.length;

                // Remove from current position and insert at new position
                selectionOrder.splice(currentIndex, 1);
                selectionOrder.splice(newIndex, 0, badgeId);

                // Update displays and check for changes
                updateNumberDisplays();
                checkBadgeChanges();
            });
        });

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
                    // Create hidden inputs for the ordered badges
                    selectionOrder.forEach((badgeId, index) => {
                        if (badgeId) {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'badges[]';
                            hiddenInput.value = badgeId;
                            badgeForm.appendChild(hiddenInput);
                        }
                    });

                    // Remove the original checkboxes from form submission
                    checkboxes.forEach(checkbox => {
                        checkbox.disabled = true;
                    });

                    showLoadingState(saveButton);
                } else {
                    e.preventDefault();
                }
            });
        }

        // Initial state check
        updateNumberDisplays();
        checkBadgeChanges();
    });
</script>
