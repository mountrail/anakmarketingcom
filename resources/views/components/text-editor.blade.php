@props(['disabled' => false, 'value' => '', 'maxchars' => 3300])

<div class="editor-container relative transition-colors duration-200">
    <textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
        'class' =>
            'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm',
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
            <label for="manual-image-upload"
                class="inline-flex items-center px-3 py-1.5 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 cursor-pointer transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Tambah Gambar
            </label>
            <input id="manual-image-upload" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                class="hidden">
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
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const uploadStatus = document.getElementById('upload-status');
            const imageGallery = document.getElementById('image-gallery');
            const imageCountWarning = document.getElementById('image-count-warning');
            const manualImageUpload = document.getElementById('manual-image-upload');
            const maxChars = {{ $maxchars }};
            const currentCount = document.getElementById('current-count');
            const remainingCount = document.getElementById('remaining-count');
            const charsRemaining = document.getElementById('chars-remaining');
            const editorContainer = document.querySelector('.editor-container');

            // Track uploaded images
            const uploadedImages = [];
            const MAX_IMAGES = 5;
            const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB in bytes

            // Function to check if dataTransfer contains image files
            function hasImageFile(dataTransfer) {
                if (!dataTransfer || !dataTransfer.types) return false;

                // Check if files are being dragged
                if (dataTransfer.types.indexOf('Files') === -1) return false;

                // If we can access files directly
                if (dataTransfer.files && dataTransfer.files.length > 0) {
                    for (let i = 0; i < dataTransfer.files.length; i++) {
                        if (dataTransfer.files[i].type.indexOf('image/') === 0) {
                            return true;
                        }
                    }
                }

                return false;
            }

            // Function to handle dropped files
            function handleDroppedFiles(files) {
                if (!files || files.length === 0) return;

                // Find image files and process them
                for (let i = 0; i < files.length; i++) {
                    if (files[i].type.indexOf('image/') === 0) {
                        uploadImage(files[i]);
                    }
                }
            }

            // Set up drag and drop for the entire editor container
            editorContainer.addEventListener('dragover', function(e) {
                if (hasImageFile(e.dataTransfer)) {
                    e.preventDefault();
                    e.stopPropagation();
                    const dragOverlay = document.getElementById('drag-overlay');
                    dragOverlay.classList.remove('hidden');
                }
            });

            editorContainer.addEventListener('dragleave', function(e) {
                const dragOverlay = document.getElementById('drag-overlay');
                dragOverlay.classList.add('hidden');
            });

            editorContainer.addEventListener('drop', function(e) {
                if (hasImageFile(e.dataTransfer)) {
                    e.preventDefault();
                    e.stopPropagation();
                    const dragOverlay = document.getElementById('drag-overlay');
                    dragOverlay.classList.add('hidden');
                    handleDroppedFiles(e.dataTransfer.files);
                }
            });

            // Function to add an image to the gallery
            window.addImageToGallery = function(imageSrc, imageId) {
                const imageContainer = document.createElement('div');
                imageContainer.className = 'relative inline-block';
                imageContainer.dataset.imageId = imageId;

                const image = document.createElement('img');
                image.src = imageSrc;
                image.className = 'max-h-32 rounded-md shadow-sm';
                image.alt = 'Uploaded image';

                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = '&times;';
                removeBtn.className =
                    'absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-md';
                removeBtn.addEventListener('click', function() {
                    // Remove from array
                    const index = uploadedImages.findIndex(img => img.id === imageId);
                    if (index > -1) {
                        uploadedImages.splice(index, 1);
                    }

                    // Remove from gallery
                    imageContainer.remove();

                    // Update count warning
                    imageCountWarning.classList.add('hidden');

                    // Update hidden input value
                    updateImagesValue();
                });

                imageContainer.appendChild(image);
                imageContainer.appendChild(removeBtn);
                imageGallery.appendChild(imageContainer);
            }

            // Function to update the hidden input with all image data
            function updateImagesValue() {
                // Create a hidden input if it doesn't exist
                let hiddenInput = document.getElementById('uploaded-images-data');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'uploaded_images';
                    hiddenInput.id = 'uploaded-images-data';
                    document.querySelector('form').appendChild(hiddenInput);
                }

                hiddenInput.value = JSON.stringify(uploadedImages);
            }

            // Function to update character count
            function updateCharacterCount(count) {
                currentCount.textContent = count;
                const remaining = maxChars - count;
                remainingCount.textContent = remaining;

                // Change color based on remaining chars
                if (remaining < 0) {
                    charsRemaining.className = 'text-red-500 dark:text-red-400 font-medium';
                } else if (remaining < maxChars * 0.1) { // Less than 10% remaining
                    charsRemaining.className = 'text-orange-500 dark:text-orange-400';
                } else {
                    charsRemaining.className = 'text-gray-600 dark:text-gray-400';
                }
            }

            // Handle manual image upload button
            manualImageUpload.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    uploadImage(this.files[0]);
                }
            });

            // Function to handle image upload
            function uploadImage(file) {
                // Check if max images reached
                if (uploadedImages.length >= MAX_IMAGES) {
                    imageCountWarning.classList.remove('hidden');
                    return;
                }

                // Check file size
                if (file.size > MAX_FILE_SIZE) {
                    uploadStatus.innerHTML = 'Error: Ukuran file melebihi 2MB';
                    uploadStatus.className = 'mt-2 text-sm text-red-500';
                    return;
                }

                uploadStatus.innerHTML = 'Memproses gambar...';
                uploadStatus.className = 'mt-2 text-sm text-blue-500';

                const formData = new FormData();
                formData.append('file', file);

                fetch('{{ route('tinymce.upload') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.location) {
                            // Store image data
                            const imageId = 'img-' + Date.now();
                            uploadedImages.push({
                                id: imageId,
                                url: data.location,
                                name: file.name
                            });

                            // Add to gallery
                            addImageToGallery(data.location, imageId);

                            // Update hidden input
                            updateImagesValue();

                            uploadStatus.innerHTML = 'Upload berhasil!';
                            uploadStatus.className = 'mt-2 text-sm text-green-500';

                            // Show warning if max reached
                            if (uploadedImages.length >= MAX_IMAGES) {
                                imageCountWarning.classList.remove('hidden');
                            }
                        } else {
                            uploadStatus.innerHTML = 'Error upload: ' + (data.error || 'Error tidak diketahui');
                            uploadStatus.className = 'mt-2 text-sm text-red-500';
                        }
                    })
                    .catch(error => {
                        uploadStatus.innerHTML = 'Error upload: ' + error;
                        uploadStatus.className = 'mt-2 text-sm text-red-500';
                    });
            }

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

                // Disable direct image functionality
                paste_data_images: false,
                automatic_uploads: false,
                images_upload_handler: function() {
                    return false; // Prevents default image upload
                },

                // Character count settings
                wordcount_countcharacters: true,

                setup: function(editor) {
                    editor.on('change keyup paste input', function() {
                        editor.save();
                        const text = editor.getContent({
                            format: 'text'
                        });
                        const count = text.length;
                        updateCharacterCount(count);
                    });

                    // Handle drag and drop events to prevent direct image insertion
                    editor.on('dragover', function(e) {
                        if (hasImageFile(e.dataTransfer)) {
                            e.preventDefault();
                        }
                    });

                    editor.on('drop', function(e) {
                        if (hasImageFile(e.dataTransfer)) {
                            e.preventDefault();
                            handleDroppedFiles(e.dataTransfer.files);
                            return false;
                        }
                    });

                    // Handle paste to remove any images
                    editor.on('PastePreProcess', function(e) {
                        // Remove any img tags from pasted content
                        e.content = e.content.replace(/<img[^>]*>/g, '');
                    });

                    // Initialize with existing content
                    editor.on('init', function() {
                        editor.setContent(editor.getElement().value);
                        const text = editor.getContent({
                            format: 'text'
                        });
                        const count = text.length;
                        updateCharacterCount(count);
                    });
                }
            });

            // Initialize any existing images in the gallery
            const uploadedImagesInput = document.getElementById('uploaded-images-data');
            if (uploadedImagesInput && uploadedImagesInput.value) {
                try {
                    const existingImages = JSON.parse(uploadedImagesInput.value);
                    existingImages.forEach(img => {
                        uploadedImages.push(img);
                        addImageToGallery(img.url, img.id);
                    });
                } catch (e) {
                    console.error('Error parsing existing images:', e);
                }
            }
        });
    </script>
@endpush
