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
                @if ($onboardingStatus['basic_profile'])
                    <div class="flex items-center justify-between bg-branding-primary rounded-lg p-4 text-white">
                        <span class="font-medium">Basic Profile</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @else
                    <div class="border-2 border-branding-primary rounded-lg p-4 bg-white cursor-pointer hover:bg-gray-50 transition-colors"
                        onclick="window.location.href='{{ route('onboarding.basic-profile') }}'">
                        <span class="font-medium text-branding-primary">Basic Profile</span>
                    </div>
                @endif

                <!-- Access Notification Center -->
                @if ($onboardingStatus['accessed_notifications'])
                    <div class="flex items-center justify-between bg-branding-primary rounded-lg p-4 text-white">
                        <span class="font-medium">Akses Notification Center</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @else
                    <div class="border-2 border-branding-primary rounded-lg p-4 bg-white cursor-pointer hover:bg-gray-50 transition-colors"
                        onclick="window.location.href='{{ route('notifications.index') }}'">
                        <span class="font-medium text-branding-primary">Akses Notification Center</span>
                    </div>
                @endif

                <!-- First Answer -->
                @if ($onboardingStatus['first_answer'])
                    <div class="flex items-center justify-between bg-branding-primary rounded-lg p-4 text-white">
                        <span class="font-medium">Ikuti Diskusi Pertamamu!</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @else
                    <div class="border-2 border-branding-primary rounded-lg p-4 bg-white cursor-pointer hover:bg-gray-50 transition-colors"
                        onclick="window.location.href='{{ route('home') }}'">
                        <span class="font-medium text-branding-primary">Ikuti Diskusi Pertamamu!</span>
                    </div>
                @endif

                <!-- First Post -->
                @if ($onboardingStatus['first_post'])
                    <div class="flex items-center justify-between bg-branding-primary rounded-lg p-4 text-white">
                        <span class="font-medium">Buat Diskusi Pertamamu!</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @else
                    <div class="border-2 border-branding-primary rounded-lg p-4 bg-white cursor-pointer hover:bg-gray-50 transition-colors"
                        onclick="window.location.href='{{ route('posts.create') }}'">
                        <span class="font-medium text-branding-primary">Buat Diskusi Pertamamu!</span>
                    </div>
                @endif

                <!-- Follow User -->
                @if ($onboardingStatus['followed_user'])
                    <div class="flex items-center justify-between bg-branding-primary rounded-lg p-4 text-white">
                        <span class="font-medium">Follow User Lain</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @else
                    <div class="border-2 border-branding-primary rounded-lg p-4 bg-white cursor-pointer hover:bg-gray-50 transition-colors"
                        onclick="window.location.href='{{ route('home') }}'">
                        <span class="font-medium text-branding-primary">Follow User Lain</span>
                    </div>
                @endif
            </div>

            <div class="space-y-3 text-center">
                @php
                    $allCompleted = collect($onboardingStatus)->every(fn($status) => $status === true);
                @endphp

                @if ($allCompleted)
                    <x-primary-button variant="primary" size="xl" class="w-full">
                        Klaim Badge!
                    </x-primary-button>
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
