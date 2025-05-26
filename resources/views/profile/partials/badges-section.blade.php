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
                                        @if ($isSelected)
                                            <div
                                                class="absolute -top-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center check-indicator">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                </label>
                                <input type="checkbox" id="badge_{{ $badge->id }}" name="badges[]"
                                    value="{{ $badge->id }}" class="hidden badge-checkbox"
                                    {{ $isSelected ? 'checked' : '' }}>
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
                    <button type="submit"
                        class="bg-branding-blue hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200"
                        id="save-badges-btn">
                        Simpan Badge
                    </button>
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
            const maxSelection = 3;

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
                    }
                });
            });

            function updateBadgeVisuals() {
                checkboxes.forEach(checkbox => {
                    const badgeContainer = checkbox.closest('.flex.flex-col');
                    const badgeIcon = badgeContainer.querySelector('.badge-icon');
                    const checkIndicator = badgeContainer.querySelector('.check-indicator');

                    if (checkbox.checked) {
                        // Selected state
                        badgeIcon.classList.remove('text-gray-400', 'dark:text-gray-600');
                        badgeIcon.classList.add('text-yellow-500');

                        // Show check indicator if it doesn't exist
                        if (!checkIndicator) {
                            const indicator = document.createElement('div');
                            indicator.className =
                                'absolute -top-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center check-indicator';
                            indicator.innerHTML =
                                '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                            badgeIcon.parentElement.appendChild(indicator);
                        }
                    } else {
                        // Unselected state
                        badgeIcon.classList.remove('text-yellow-500');
                        badgeIcon.classList.add('text-gray-400', 'dark:text-gray-600');

                        // Remove check indicator
                        if (checkIndicator) {
                            checkIndicator.remove();
                        }
                    }
                });
            }
        });
    </script>
@endif
