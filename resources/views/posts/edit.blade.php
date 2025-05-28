{{-- resources\views\posts\edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Pertanyaan / Diskusi') }}
        </h2>
    </x-slot>

    <!-- Toast Component for Success/Error Messages -->
    @if (session('toast'))
        <x-toast :type="session('toast.type')" :message="session('toast.message')" :duration="session('toast.duration', 4000)" />
    @endif

    <!-- Validation Error Toast -->
    @if ($errors->any())
        <x-toast type="error" message="Terdapat kesalahan dalam form. Silakan periksa field yang ditandai."
            :duration="5000" />
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('posts.update', $post) }}" class="space-y-6"
                        enctype="multipart/form-data" id="edit-form">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="title" :value="__('Judul')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                value="{{ old('title', $post->title) }}" required
                                placeholder="Tulis pertanyaan / diskusi utama di sini.." />
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Kategori')" />
                            <select name="type" id="type"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full">
                                <option value="question" {{ old('type', $post->type) == 'question' ? 'selected' : '' }}>
                                    Pertanyaan
                                </option>
                                <option value="discussion"
                                    {{ old('type', $post->type) == 'discussion' ? 'selected' : '' }}>Diskusi
                                </option>
                            </select>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="content" :value="__('Deskripsi')" />
                            <x-text-editor id="content" name="content" :value="old('content', $post->content)" />
                            @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hidden field to store uploaded images -->
                        <input type="hidden" name="uploaded_images" id="uploaded-images-data" value="">

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('posts.show', $post->slug) }}"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:bg-gray-400 dark:focus:bg-gray-500 active:bg-gray-500 dark:active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Kembali') }}
                            </a>

                            <x-primary-button type="submit" variant="primary" size="md" class="ml-4"
                                id="update-button">
                                <span id="update-text">{{ __('Simpan Perubahan') }}</span>
                                <span id="update-loading" class="hidden">
                                    <x-loading-spinner size="sm" color="white" />
                                    <span class="ml-2">Menyimpan...</span>
                                </span>
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Initialize existing uploaded images on page load
            document.addEventListener('DOMContentLoaded', function() {
                // Load existing images from the post
                @if ($post->images->count() > 0)
                    const existingImages = @json(
                        $post->images->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'url' => $image->url,
                                'name' => $image->name,
                            ];
                        }));

                    existingImages.forEach(img => {
                        addImageToGallery(img.url, img.id, img.name);
                    });
                @endif

                // Initialize uploaded images data if available
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

                // Form submission handling
                const form = document.getElementById('edit-form');
                const updateButton = document.getElementById('update-button');
                const updateText = document.getElementById('update-text');
                const updateLoading = document.getElementById('update-loading');

                form.addEventListener('submit', function(e) {
                    // Show loading state
                    updateButton.disabled = true;
                    updateText.classList.add('hidden');
                    updateLoading.classList.remove('hidden');

                    // Client-side validation
                    const title = document.getElementById('title');
                    const content = document.getElementById('content');

                    let hasErrors = false;

                    // Validate title
                    if (!title.value.trim()) {
                        showValidationToast('Judul harus diisi');
                        hasErrors = true;
                    }

                    // Validate content (basic check)
                    if (!content.value.trim()) {
                        showValidationToast('Deskripsi harus diisi');
                        hasErrors = true;
                    }

                    if (hasErrors) {
                        e.preventDefault();
                        resetUpdateButton();
                        return false;
                    }

                    // Show processing toast
                    toast('Sedang menyimpan perubahan...', 'info', {
                        duration: 0, // Don't auto-hide
                        dismissible: false
                    });
                });

                // Reset update button function
                function resetUpdateButton() {
                    updateButton.disabled = false;
                    updateText.classList.remove('hidden');
                    updateLoading.classList.add('hidden');
                }

                // Show validation toast
                function showValidationToast(message) {
                    toast(message, 'error', {
                        duration: 4000,
                        position: 'top-right'
                    });
                }

                // Reset button state if there are server validation errors
                @if ($errors->any())
                    resetUpdateButton();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
