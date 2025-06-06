{{-- resources/views/posts/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Pertanyaan / Diskusi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('posts.store') }}" class="space-y-6" enctype="multipart/form-data"
                        id="post-form">
                        @csrf

                        <div>
                            <x-input-label for="title" :value="__('Judul')" class="font-bold text-lg" />
                            <x-text-input id="title" name="title" type="text"
                                class="mt-1 block w-full border-essentials-inactive dark:bg-essentials-inactive/20 dark:border-essentials-inactive dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                value="{{ old('title') }}" required
                                placeholder="Tulis pertanyaan / diskusi utama di sini.." />
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Kategori')" class="font-bold text-lg" />
                            <x-select-input id="type" name="type" :selected="old('type', 'question')" :options="['question' => 'Pertanyaan', 'discussion' => 'Diskusi']"
                                placeholder="Pilih kategori"
                                class="mt-1 w-48 border-essentials-inactive dark:bg-essentials-inactive/20 dark:border-essentials-inactive dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="content" :value="__('Deskripsi')" />
                            @include('posts.partials.tinymce-editor', [
                                'name' => 'content',
                                'id' => 'content',
                                'value' => old('content'),
                                'maxchars' => 3300,
                            ])
                            @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="relative">
                            @include('posts.partials.image-upload', [
                                'name' => 'uploaded_images',
                                'existingImages' => old('uploaded_images', ''),
                            ])
                        </div>

                        <div class="flex items-center justify-end mt-4 gap-3">
                            <x-primary-button type="button" variant="inactive" size="md"
                                onclick="window.location.href='{{ route('home') }}'">
                                {{ __('Kembali') }}
                            </x-primary-button>

                            <x-primary-button type="submit" id="submit-btn" variant="primary" size="md">
                                {{ __('Post') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Simple form submission - no loading spinner
                const form = document.getElementById('post-form');
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
</x-app-layout>
