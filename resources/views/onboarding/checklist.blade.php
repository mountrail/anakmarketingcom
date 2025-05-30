{{-- resources\views\onboarding\checklist.blade.php --}}
@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Checklist Screen -->
        <div>
            <!-- Back Button -->
            <div class="mb-6">
                <button onclick="window.location.href='{{ route('home') }}'"
                    class="flex items-center text-gray-600 hover:text-branding-primary transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                    Back
                </button>
            </div>

            <div class="text-center mb-6">
                <p class="text-gray-700 font-medium mb-2">
                    Klik yang belum untuk menyelesaikan, dan dapatkan <strong>badges baru!</strong>
                </p>
                <h2 class="text-xl font-bold text-branding-primary">
                    Onboarding Checklist
                </h2>
            </div>

            <div class="space-y-3 mb-8">
                <!-- Basic Profile -->
                <x-onboarding.checklist-item title="Basic Profile" :completed="$onboardingStatus['basic_profile']" :route="route('onboarding.basic-profile')" />

                <!-- Access Notification Center -->
                <x-onboarding.checklist-item title="Akses Notification Center" :completed="$onboardingStatus['accessed_notifications']" :route="route('notifications.index')" />

                <!-- First Discussion Participation (Answer OR Vote) -->
                <x-onboarding.checklist-item title="Ikuti Diskusi Pertamamu!" :completed="$onboardingStatus['first_answer']" :route="route('onboarding.discussion-list')" />

                <!-- First Post -->
                <x-onboarding.checklist-item title="Buat Diskusi Pertamamu!" :completed="$onboardingStatus['first_post']" :route="route('onboarding.first-post')" />

                <!-- Follow User -->
                <x-onboarding.checklist-item title="Follow User Lain" :completed="$onboardingStatus['followed_user']" :route="route('onboarding.follow-users')" />
            </div>

            <div class="space-y-3 text-center">
                @php
                    $allCompleted = collect($onboardingStatus)->every(fn($status) => $status === true);
                @endphp

                @if ($allCompleted)
                    <form action="{{ route('onboarding.claim-badge') }}" method="POST" class="w-full">
                        @csrf
                        <x-primary-button type="submit" variant="primary" size="xl" class="w-full">
                            Klaim Badge!
                        </x-primary-button>
                    </form>
                @else
                    <x-primary-button variant="inactive" size="xl" disabled class="w-full">
                        Klaim Badge!
                    </x-primary-button>
                @endif

                <x-primary-button variant="dark" size="xl" onclick="window.location.href='{{ route('home') }}'"
                    class="w-full">
                    ke Halaman Utama
                </x-primary-button>
            </div>
        </div>
    </div>
@endsection
