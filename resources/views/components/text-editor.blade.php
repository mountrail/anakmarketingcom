@props(['disabled' => false, 'value' => '', 'maxchars' => 3300])

<div class="editor-container relative transition-colors duration-200">
    <textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
        'class' =>
            'bg-essentials-inactive/20 border-essentials-inactive dark:bg-essentials-inactive/20 dark:border-essentials-inactive dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm',
    ]) !!}>{{ $value }}</textarea>

    <div id="character-count" class="mt-2 text-sm flex justify-between">
        <span class="text-gray-600 dark:text-gray-400">Karakter: <span id="current-count">0</span>/<span
                id="max-count">{{ $maxchars }}</span></span>
        <span id="chars-remaining" class="text-gray-600 dark:text-gray-400">Tersisa: <span
                id="remaining-count">{{ $maxchars }}</span></span>
    </div>

    <div class="mt-4">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Unggah Gambar (maksimal 5 gambar, masing-masing ukuran
            maksimal 2MB)</p>
        <div id="image-gallery" class="flex flex-wrap gap-4 my-2"></div>
        <div class="mt-2">
            <label for="image-upload"
                class="inline-flex items-center px-3 py-1.5 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 cursor-pointer transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 002 2z" />
                </svg>
                Tambah Gambar
            </label>
            <input id="image-upload" name="images[]" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                multiple class="hidden">
        </div>
        <div id="upload-status" class="mt-2 text-sm"></div>
        <div id="image-count-warning" class="mt-2 text-sm text-red-500 hidden">Maksimal 5 gambar diperbolehkan.</div>
    </div>

    <!-- Drag overlay -->
    <div id="drag-overlay"
        class="hidden absolute inset-0 bg-orange-100/70 dark:bg-orange-900/30 border-2 border-dashed border-orange-400 rounded-md z-10 flex items-center justify-center pointer-events-none">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-lg">
            <div class="flex flex-col items-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-12 w-12 text-branding-primary dark:text-orange-400 mb-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 002 2z" />
                </svg>
                <span class="text-lg font-medium text-branding-primary dark:text-orange-400">Tambahkan media</span>
                <span class="text-sm text-gray-600 dark:text-gray-400 mt-1">Lepaskan gambar di sini</span>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadStatus = document.getElementById('upload-status');
            const imageGallery = document.getElementById('image-gallery');
            const imageCountWarning = document.getElementById('image-count-warning');
            const imageUpload = document.getElementById('image-upload');
            const maxChars = {{ $maxchars }};
            const currentCount = document.getElementById('current-count');
            const remainingCount = document.getElementById('remaining-count');
            const charsRemaining = document.getElementById('chars-remaining');
            const editorContainer = document.querySelector('.editor-container');

            const MAX_IMAGES = 5;
            const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB

            let imageCount = 0;
            const selectedFiles = new Map(); // Track selected files

            // Function to validate and preview images
            function handleFiles(files) {
                const fileArray = Array.from(files);
                const imageFiles = fileArray.filter(file => file.type.startsWith('image/'));

                // Count existing images (for edit mode)
                const existingImagesCount = document.querySelectorAll(
                    '.existing-image-container:not(.marked-for-deletion)').length;
                const totalImages = imageCount + imageFiles.length + existingImagesCount;

                if (totalImages > MAX_IMAGES) {
                    imageCountWarning.classList.remove('hidden');
                    uploadStatus.innerHTML = `Error: Maksimal ${MAX_IMAGES} gambar diperbolehkan`;
                    uploadStatus.className = 'mt-2 text-sm text-red-500';
                    return;
                }

                imageFiles.forEach(file => {
                    if (file.size > MAX_FILE_SIZE) {
                        uploadStatus.innerHTML = `Error: File ${file.name} melebihi 2MB`;
                        uploadStatus.className = 'mt-2 text-sm text-red-500';
                        return;
                    }

                    // Generate unique ID for the file
                    const fileId = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    selectedFiles.set(fileId, file);
                    previewImage(file, fileId);
                });

                // Update the file input with all selected files
                updateFileInput();
            }

            // Function to preview image before upload
            function previewImage(file, fileId) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageContainer = document.createElement('div');
                    imageContainer.className = 'relative inline-block new-image-container';
                    imageContainer.dataset.fileId = fileId;

                    const image = document.createElement('img');
                    image.src = e.target.result;
                    image.className = 'max-h-32 rounded-md shadow-sm';
                    image.alt = 'Preview image';

                    const removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.type = 'button';
                    removeBtn.className =
                        'absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-md hover:bg-red-600';

                    removeBtn.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Remove from selected files
                        selectedFiles.delete(fileId);

                        // Remove the preview
                        imageContainer.remove();
                        imageCount--;
                        imageCountWarning.classList.add('hidden');
                        uploadStatus.innerHTML = '';

                        // Update file input
                        updateFileInput();
                    });

                    imageContainer.appendChild(image);
                    imageContainer.appendChild(removeBtn);
                    imageGallery.appendChild(imageContainer);

                    imageCount++;
                };
                reader.readAsDataURL(file);
            }

            // Function to update the file input with selected files
            function updateFileInput() {
                const dt = new DataTransfer();
                selectedFiles.forEach(file => {
                    dt.items.add(file);
                });
                imageUpload.files = dt.files;
            }

            // Function to update character count
            function updateCharacterCount(count) {
                currentCount.textContent = count;
                const remaining = maxChars - count;
                remainingCount.textContent = remaining;

                if (remaining < 0) {
                    charsRemaining.className = 'text-red-500 dark:text-red-400 font-medium';
                } else if (remaining < maxChars * 0.1) {
                    charsRemaining.className = 'text-orange-500 dark:text-orange-400';
                } else {
                    charsRemaining.className = 'text-gray-600 dark:text-gray-400';
                }
            }

            // Handle file input change
            imageUpload.addEventListener('change', function() {
                if (this.files.length > 0) {
                    handleFiles(this.files);
                }
            });

            // Drag and drop functionality
            function hasImageFile(dataTransfer) {
                if (!dataTransfer || !dataTransfer.types) return false;
                if (dataTransfer.types.indexOf('Files') === -1) return false;

                if (dataTransfer.files && dataTransfer.files.length > 0) {
                    for (let i = 0; i < dataTransfer.files.length; i++) {
                        if (dataTransfer.files[i].type.startsWith('image/')) {
                            return true;
                        }
                    }
                }
                return false;
            }

            editorContainer.addEventListener('dragover', function(e) {
                if (hasImageFile(e.dataTransfer)) {
                    e.preventDefault();
                    e.stopPropagation();
                    document.getElementById('drag-overlay').classList.remove('hidden');
                }
            });

            editorContainer.addEventListener('dragleave', function(e) {
                document.getElementById('drag-overlay').classList.add('hidden');
            });

            editorContainer.addEventListener('drop', function(e) {
                if (hasImageFile(e.dataTransfer)) {
                    e.preventDefault();
                    e.stopPropagation();
                    document.getElementById('drag-overlay').classList.add('hidden');

                    handleFiles(e.dataTransfer.files);
                }
            });

            // Initialize TinyMCE
            tinymce.init({
                selector: 'textarea{{ $attributes->get('id') ? '#' . $attributes->get('id') : '' }}',
                plugins: 'lists wordcount',
                toolbar: 'bold italic underline | bullist',
                menubar: false,
                height: 300,
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                entity_encoding: 'raw',
                branding: false,
                promotion: false,
                convert_urls: false,
                relative_urls: false,
                remove_script_host: false,
                max_chars: maxChars,
                paste_data_images: false,
                automatic_uploads: false,
                wordcount_countcharacters: true,

                setup: function(editor) {
                    editor.on('change keyup paste input', function() {
                        editor.save();
                        const text = editor.getContent({
                            format: 'text'
                        });
                        updateCharacterCount(text.length);
                    });

                    // Prevent image insertion in editor
                    editor.on('dragover drop paste', function(e) {
                        if (e.type === 'paste' || hasImageFile(e.dataTransfer)) {
                            e.preventDefault();
                        }
                    });

                    editor.on('PastePreProcess', function(e) {
                        e.content = e.content.replace(/<img[^>]*>/g, '');
                    });

                    editor.on('init', function() {
                        editor.setContent(editor.getElement().value);
                        const text = editor.getContent({
                            format: 'text'
                        });
                        updateCharacterCount(text.length);
                    });
                }
            });
        });
    </script>
@endpush
