{{-- resources/views/onboarding/first-post.blade.php --}}
@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Buat pertanyaan atau diskusi baru<br>
                dengan mengakses Home
            </h1>
            <p class="text-gray-600 font-medium">
                Kami bantu untuk yang pertama!
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-branding-primary mb-6">
                Buat Pertanyaan / Diskusi Baru
            </h2>

            <form method="POST" action="{{ route('posts.store') }}" class="space-y-6" id="first-post-form">
                @csrf

                <!-- Title Field -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Judul
                    </label>
                    <input type="text" id="title" name="title"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-branding-primary focus:ring-branding-primary focus:ring-1 bg-gray-50"
                        placeholder="Tulis pertanyaan / diskusi utama di sini.." value="{{ old('title') }}" required />
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category Field -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Kategori
                    </label>
                    <div class="relative">
                        <select id="type" name="type"
                            class="w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-branding-primary focus:ring-branding-primary focus:ring-1 bg-gray-50 appearance-none">
                            <option value="question" {{ old('type', 'question') == 'question' ? 'selected' : '' }}>
                                Pertanyaan
                            </option>
                            <option value="discussion" {{ old('type') == 'discussion' ? 'selected' : '' }}>
                                Diskusi
                            </option>
                        </select>
                    </div>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description Field -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <x-textarea id="content" name="content" rows="6"
                        class="w-full border-gray-300 focus:border-branding-primary focus:ring-branding-primary focus:ring-1 bg-gray-50"
                        placeholder="Detail pertanyaan atau diskusi Anda. Sertakan data pendukung seperti gambar apabila diperlukan"
                        required>{{ old('content') }}</x-textarea>
                    @error('content')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="window.location.href='{{ route('onboarding.checklist') }}'"
                        class="flex-1 px-4 py-3 text-white bg-gray-600 rounded-md font-medium hover:bg-gray-700 transition-colors">
                        Kembali
                    </button>
                    <button type="submit" id="submit-btn"
                        class="flex-1 px-4 py-3 text-white bg-branding-primary rounded-md font-medium hover:bg-orange-600 transition-colors disabled:opacity-75 disabled:cursor-not-allowed">
                        Post
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('first-post-form');
                const submitBtn = document.getElementById('submit-btn');

                form.addEventListener('submit', function(e) {
                    if (form.checkValidity()) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                        submitBtn.textContent = 'Posting...';
                    }
                });

                @if ($errors->any())
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    submitBtn.textContent = 'Post';
                @endif

                window.addEventListener('pageshow', function(e) {
                    if (e.persisted) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                        submitBtn.textContent = 'Post';
                    }
                });
            });
        </script>
    @endpush
@endsection
