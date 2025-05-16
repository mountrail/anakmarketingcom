@props(['disabled' => false, 'value' => ''])

<div class="editor-container">
    <textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
        'class' =>
            'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm',
    ]) !!}>{{ $value }}</textarea>

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

            // Track uploaded images
            const uploadedImages = [];
            const MAX_IMAGES = 5;
            const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB in bytes

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
                plugins: 'lists',
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

                // Image settings
                image_dimensions: false,
                object_resizing: 'img',

                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });

                    // Initialize with existing content
                    editor.on('init', function() {
                        editor.setContent(editor.getElement().value);
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
