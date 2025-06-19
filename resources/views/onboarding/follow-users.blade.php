{{-- resources\views\onboarding\follow-users.blade.php --}}
@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="max-w-2xl mx-auto px-0 sm:px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8 px-2 sm:px-0">
            <h1 class="text-lg font-medium text-gray-800 mb-2">
                Ikuti user lain agar tidak<br>
                ketinggalan diskusi seru
            </h1>
            <h2 class="text-xl font-bold text-branding-primary mt-4">
                Rekomendasi Kami
            </h2>
        </div>

        <!-- Users List (No padding on main div, no space between items) -->
        <div class="mb-8">
            @if ($recommendedUsers->count() > 0)
                @foreach ($recommendedUsers as $user)
                    @php
                        $displayedBadges = \App\Services\BadgeService::getDisplayedBadges($user);

                        // Create job description with proper truncation
                        $jobDescription = '';
                        if ($user->job_title && $user->company) {
                            $jobDescription = $user->job_title . ' at ' . $user->company;
                        } elseif ($user->job_title) {
                            $jobDescription = $user->job_title;
                        } elseif ($user->company) {
                            $jobDescription = $user->company;
                        } else {
                            $jobDescription = 'Member';
                        }

                        // Truncate if too long (adjust the length as needed)
                        $maxLength = 50;
                        $truncatedJobDescription =
                            strlen($jobDescription) > $maxLength
                                ? substr($jobDescription, 0, $maxLength) . '...'
                                : $jobDescription;
                    @endphp

                    <div class="bg-white border-b border-gray-200 last:border-b-0 p-4 sm:p-6">
                        <div class="flex items-center justify-between gap-4">
                            <!-- User Info -->
                            <div class="flex items-center space-x-3 flex-1 min-w-0">
                                <!-- Profile Picture -->
                                <div class="flex-shrink-0">
                                    <a href="{{ route('profile.show', $user) }}">
                                        <img src="{{ $user->getProfileImageUrl() }}" alt="{{ $user->name }}"
                                            class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 hover:opacity-80 transition-opacity">
                                    </a>
                                </div>

                                <!-- User Details -->
                                <div class="flex-1 min-w-0">
                                    <!-- Name -->
                                    <a href="{{ route('profile.show', $user) }}"
                                        class="text-base font-semibold text-gray-900 hover:text-branding-primary transition-colors truncate block">
                                        {{ $user->name }}
                                    </a>

                                    <!-- Job Title & Company with tooltip for full text -->
                                    <div class="relative group">
                                        <p class="text-sm text-gray-600 truncate">
                                            {{ $truncatedJobDescription }}
                                        </p>

                                        <!-- Tooltip for full job description (only show if truncated) -->
                                        @if (strlen($jobDescription) > $maxLength)
                                            <div
                                                class="absolute bottom-full left-0 right-0 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10 pointer-events-none">
                                                <div class="text-left">{{ $jobDescription }}</div>
                                                {{-- Tooltip arrow --}}
                                                <div
                                                    class="absolute top-full left-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900">
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- User Badges -->
                                    <div class="flex items-center space-x-1 mt-1">
                                        @if ($displayedBadges && $displayedBadges->count() > 0)
                                            <x-badge-preview :badges="$displayedBadges" :user="$user" badgeSize="7" />
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Follow Button -->
                            <div class="flex-shrink-0">
                                @php
                                    $isFollowing = auth()->check() && auth()->user()->isFollowing($user);
                                @endphp

                                <x-primary-button id="follow-btn-{{ $user->id }}" data-user-id="{{ $user->id }}"
                                    data-following="{{ $isFollowing ? 'true' : 'false' }}"
                                    variant="{{ $isFollowing ? 'secondary' : 'primary' }}" size="sm"
                                    onclick="toggleFollow({{ $user->id }}, this)" class="whitespace-nowrap">

                                    <svg id="follow-loading-{{ $user->id }}"
                                        class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-current"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>

                                    <span id="follow-btn-text-{{ $user->id }}">
                                        {{ $isFollowing ? 'Following' : 'Follow' }}
                                    </span>
                                </x-primary-button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- No Users Available -->
                <div class="text-center py-8 bg-white border border-gray-200">
                    <div class="text-gray-500 text-lg mb-2">
                        Belum ada user dengan post yang tersedia
                    </div>
                    <p class="text-gray-400 text-sm">
                        Coba kembali lagi nanti!
                    </p>
                </div>
            @endif
        </div>

        <!-- Bottom Navigation Buttons -->
        <div class="flex space-x-3 px-2 sm:px-0">
            <!-- Main Home Button -->
            <x-primary-button onclick="window.location.href='{{ route('home') }}'" variant="dark" size="md"
                class="flex-1">
                ke Halaman Utama
            </x-primary-button>
            <!-- Back to Checklist Button -->
            <x-primary-button onclick="window.location.href='{{ route('onboarding.checklist') }}'" variant="primary"
                size="md" class="flex-1">
                Kembali ke Checklist
            </x-primary-button>

        </div>
    </div>

    <!-- Include the follow system script -->
    @include('profile.components.scripts.follow-system', ['user' => auth()->user()])
@endsection

@push('scripts')
    <script>
        async function toggleFollow(userId, button) {
            const loadingSpinner = document.getElementById(`follow-loading-${userId}`);
            const buttonText = document.getElementById(`follow-btn-text-${userId}`);

            try {
                // Show loading state
                button.disabled = true;
                loadingSpinner.classList.remove('hidden');

                const response = await fetch(`/follow/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Update button appearance and text
                    button.dataset.following = data.isFollowing ? 'true' : 'false';
                    buttonText.textContent = data.buttonText;

                    // Update button variant classes
                    if (data.isFollowing) {
                        // Change to secondary variant (Following state)
                        button.className = button.className.replace(/bg-branding-primary|hover:bg-branding-primary\/90/,
                            'bg-gray-500 hover:bg-red-500');
                    } else {
                        // Change to primary variant (Follow state)
                        button.className = button.className.replace(/bg-gray-500|hover:bg-red-500/,
                            'bg-branding-primary hover:bg-branding-primary/90');
                    }

                    // Show success message (optional)
                    if (window.showToast) {
                        window.showToast(data.message, 'success');
                    }
                } else {
                    throw new Error(data.error || 'Failed to toggle follow');
                }
            } catch (error) {
                console.error('Error toggling follow:', error);

                // Show error message
                if (window.showToast) {
                    window.showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
                } else {
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                }
            } finally {
                // Hide loading state and re-enable button
                loadingSpinner.classList.add('hidden');
                button.disabled = false;
            }
        }

        // Add hover effects for Following buttons
        document.addEventListener('DOMContentLoaded', function() {
            const followButtons = document.querySelectorAll('[data-following="true"]');

            followButtons.forEach(button => {
                const userId = button.dataset.userId;
                const buttonText = document.getElementById(`follow-btn-text-${userId}`);

                button.addEventListener('mouseenter', function() {
                    if (this.dataset.following === 'true') {
                        buttonText.textContent = 'Unfollow';
                    }
                });

                button.addEventListener('mouseleave', function() {
                    if (this.dataset.following === 'true') {
                        buttonText.textContent = 'Following';
                    }
                });
            });
        });
    </script>
@endpush
