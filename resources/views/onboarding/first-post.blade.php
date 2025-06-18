{{-- resources/views/onboarding/first-post.blade.php --}}
@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-xl font-bold text-gray-900 mb-2">
                Buat pertanyaan atau diskusi baru<br>
                dengan mengakses Home
            </h1>
            <p class="font-medium">
                Kami bantu untuk yang pertama!
            </p>
        </div>

        <!-- Form -->
        <div class="space-y-6">
            <h2 class="text-xl font-bold text-center text-branding-primary">
                Buat Pertanyaan / Diskusi Baru
            </h2>

            <x-post-form :action="route('posts.store')" :back-url="route('onboarding.checklist')" submit-text="Post" />
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Form submission handling
                const form = document.getElementById('first-post-form');
                const submitBtn = document.getElementById('submit-btn');

                form.addEventListener('submit', function(e) {
                    if (form.checkValidity()) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    }
                });

                @if ($errors->any())
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                @endif

                window.addEventListener('pageshow', function(e) {
                    if (e.persisted) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    }
                });
            });
        </script>
    @endpush
@endsection
