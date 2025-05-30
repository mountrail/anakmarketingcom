{{-- resources\views\onboarding\badge-earned.blade.php --}}
@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="max-w-md mx-auto px-4 py-8">
        <div class="text-center">
            <!-- Congratulations Message -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-branding-primary mb-2">
                    Selamat!
                </h1>
                <h2 class="text-xl font-bold text-branding-primary mb-4">
                    @if ($badge->name === 'Marketers Onboard!')
                        Kamu telah menyelesaikan semua misi onboarding!
                    @else
                        Kamu mendapatkan <em>badge</em> pertamamu!
                    @endif
                </h2>
            </div>

            <!-- Badge Display -->
            <div class="mb-8">
                <div class="flex justify-center mb-4">
                    <div class="w-24 h-24 flex items-center justify-center badge-container">
                        <x-icons.badge class="w-20 h-20 text-gray-800 badge-icon" />
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-2 badge-title">
                    {{ $badge->name }}
                </h3>
                <p class="text-gray-600 text-sm leading-relaxed badge-description">
                    {{ $badge->description }}
                </p>
            </div>

            <!-- Description -->
            <div class="mb-8">
                <p class="text-gray-700 text-sm leading-relaxed">
                    @if ($badge->name === 'Marketers Onboard!')
                        Selamat datang di komunitas! Mulai berbagi dan berdiskusi untuk mendapatkan lebih banyak
                        <em>badges</em> dan benefit menarik lainnya.
                    @else
                        Beri kontribusi pada komunitas ini untuk dapatkan <em>badges</em> dan banyak benefit menarik lainnya
                    @endif
                </p>
            </div>

            <!-- Continue Button -->
            <div class="flex justify-center continue-button">
                @if ($badge->name === 'Marketers Onboard!')
                    <x-primary-button type="button" onclick="window.location.href='{{ route('home') }}'" variant="primary"
                        size="xl" class="w-auto px-8">
                        Mulai Berdiskusi
                    </x-primary-button>
                @elseif ($badge->name === 'Ikutan Nimbrung' && session('return_to_post'))
                    <x-primary-button type="button"
                        onclick="window.location.href='{{ route('posts.show', session('return_to_post')) }}'"
                        variant="primary" size="xl" class="w-auto px-8">
                        Lanjutkan
                    </x-primary-button>
                @else
                    <x-primary-button type="button" onclick="window.location.href='{{ route('onboarding.checklist') }}'"
                        variant="primary" size="xl" class="w-auto px-8">
                        Lanjutkan
                    </x-primary-button>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Initial states - hidden/scaled down */
        .badge-container {
            opacity: 0;
            transform: scale(0.3) rotate(-180deg);
            transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .badge-title {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out 0.4s;
        }

        .badge-description {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out 0.6s;
        }

        .continue-button {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out 0.8s;
        }

        /* Animated states */
        .badge-container.animate {
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }

        .badge-title.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .badge-description.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .continue-button.animate {
            opacity: 1;
            transform: translateY(0);
        }

        /* Badge pulse animation after main animation */
        .badge-icon.pulse {
            animation: badge-pulse 2s infinite;
        }

        @keyframes badge-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        /* Celebration sparkle effect */
        .badge-container::before {
            content: '';
            position: absolute;
            top: -10px;
            right: -10px;
            width: 20px;
            height: 20px;
            background: radial-gradient(circle, #fbbf24, transparent);
            border-radius: 50%;
            opacity: 0;
            animation: sparkle 1.5s ease-out 0.8s;
        }

        .badge-container::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: -5px;
            width: 15px;
            height: 15px;
            background: radial-gradient(circle, #f59e0b, transparent);
            border-radius: 50%;
            opacity: 0;
            animation: sparkle 1.5s ease-out 1.2s;
        }

        @keyframes sparkle {
            0% {
                opacity: 0;
                transform: scale(0) rotate(0deg);
            }

            50% {
                opacity: 1;
                transform: scale(1) rotate(180deg);
            }

            100% {
                opacity: 0;
                transform: scale(0) rotate(360deg);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Trigger animations on page load
            setTimeout(() => {
                document.querySelector('.badge-container').classList.add('animate');
            }, 200);

            setTimeout(() => {
                document.querySelector('.badge-title').classList.add('animate');
            }, 600);

            setTimeout(() => {
                document.querySelector('.badge-description').classList.add('animate');
            }, 800);

            setTimeout(() => {
                document.querySelector('.continue-button').classList.add('animate');
            }, 1000);

            // Add pulse animation to badge after main animation completes
            setTimeout(() => {
                document.querySelector('.badge-icon').classList.add('pulse');
            }, 1200);
        });
    </script>
@endsection
