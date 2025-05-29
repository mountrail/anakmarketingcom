{{-- resources\views\onboarding\welcome.blade.php --}}
@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Welcome Screen -->
        <div class="text-center">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-branding-primary mb-4">
                    Selamat datang di anakMarketing.com!
                </h1>
                <p class="text-gray-700 leading-relaxed">
                    Sebelum mulai, ayo ikuti <strong>onboarding process</strong> ini untuk pengalaman lebih baik dan
                    meraih
                    <strong>badge</strong> pertamamu!
                </p>
            </div>

            <x-primary-button variant="primary" size="lg"
                onclick="window.location.href='{{ route('onboarding.basic-profile') }}'" class="w-auto px-8">
                Lanjutkan
            </x-primary-button>
        </div>
    </div>
@endsection
