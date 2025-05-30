{{-- resources\views\onboarding\badge-earned.blade.php --}}
@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="max-w-md mx-auto px-4 py-8">
        <div class="text-center">
            <!-- Congratulations Message -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-orange-500 mb-2">
                    Selamat!
                </h1>
                <h2 class="text-xl font-bold text-orange-500 mb-4">
                    Kamu mendapatkan <em>badge</em> pertamamu!
                </h2>
            </div>

            <!-- Badge Display -->
            <div class="mb-8">
                <div class="flex justify-center mb-4">
                    <div class="w-24 h-24 flex items-center justify-center">
                        <x-icons.badge class="w-20 h-20 text-gray-800" />
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    {{ $badge->name }}
                </h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {{ $badge->description }}
                </p>
            </div>

            <!-- Description -->
            <div class="mb-8">
                <p class="text-gray-700 text-sm leading-relaxed">
                    Beri kontribusi pada komunitas ini untuk dapatkan <em>badges</em> dan banyak benefit menarik lainnya
                </p>
            </div>

            <!-- Continue Button -->
            <div class="flex justify-center">
                <x-primary-button type="button" onclick="window.location.href='{{ route('onboarding.checklist') }}'"
                    size="xl"
                    class="w-auto px-8 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors duration-200">
                    Lanjutkan
                </x-primary-button>
            </div>
        </div>
    </div>
@endsection
