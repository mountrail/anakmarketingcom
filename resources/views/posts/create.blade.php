{{-- resources\views\posts\create.blade.php --}}
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
                            <x-input-label for="title" :value="__('Judul')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                value="{{ old('title') }}" required
                                placeholder="Tulis pertanyaan / diskusi utama di sini.." />
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Kategori')" />
                            <select name="type" id="type"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full">
                                <option value="question" {{ old('type') == 'question' ? 'selected' : '' }}>Pertanyaan
                                </option>
                                <option value="discussion" {{ old('type') == 'discussion' ? 'selected' : '' }}>Diskusi
                                </option>
                            </select>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="content" :value="__('Deskripsi')" />
                            <x-text-editor id="content" name="content" :value="old('content')" />
                            @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hidden field to store uploaded images -->
                        <input type="hidden" name="uploaded_images" id="uploaded-images-data"
                            value="{{ old('uploaded_images') }}">

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
                // Initialize any existing uploaded images on page load
                const uploadedImagesData = document.getElementById('uploaded-images-data').value;
                if (uploadedImagesData) {
                    try {
                        const images = JSON.parse(uploadedImagesData);
                        images.forEach(img => {
                            addImageToGallery(img.url, img.id);
                        });
                    } catch (e) {
                        console.error('Error loading saved images:', e);
                    }
                }

                // Simple form submission - no loading spinner
                const form = document.getElementById('post-form');
                const submitBtn = document.getElementById('submit-btn');

                form.addEventListener('submit', function(e) {
                    // Just disable the button to prevent double submission
                    if (form.checkValidity()) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    }
                });

                // Reset button state if there are validation errors and page reloads
                @if ($errors->any())
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                @endif

                // Handle back/forward navigation
                window.addEventListener('pageshow', function(e) {
                    if (e.persisted) {
                        // Reset button state when page comes from cache
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
