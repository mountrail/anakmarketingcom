{{-- resources/views/posts/partials/image-upload.blade.php --}}
@props(['name' => 'uploaded_images', 'existingImages' => ''])

<div class="image-upload-container">
    <div class="mt-4">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
            Unggah Gambar (maksimal 5 gambar, masing-masing ukuran maksimal 2MB)
        </p>

        <div class="image-gallery flex flex-wrap gap-4 my-2"></div>

        <div class="mt-2">
            <label for="manual-image-upload"
                class="inline-flex items-center px-3 py-1.5 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 cursor-pointer transition">
                <x-icons.image class="w-4 h-4 mr-1" />
                Tambah Gambar
            </label>
            <input id="manual-image-upload" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                class="hidden" multiple>
        </div>

        <div class="upload-status mt-2 text-sm"></div>
        <div class="image-count-warning mt-2 text-sm text-red-500 hidden">
            Maksimal 5 gambar diperbolehkan.
        </div>
    </div>

    <!-- Drag overlay -->
    <div
        class="drag-overlay hidden absolute inset-0 bg-orange-100/70 dark:bg-orange-900/30 border-2 border-dashed border-orange-400 rounded-md z-10 flex items-center justify-center pointer-events-none">
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

    <!-- Hidden field to store uploaded images -->
    <input type="hidden" name="{{ $name }}" class="uploaded-images-data" value="{{ $existingImages }}">
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const imageUploadContainer = document.querySelector('.image-upload-container');
            const uploadStatus = imageUploadContainer.querySelector('.upload-status');
            const imageGallery = imageUploadContainer.querySelector('.image-gallery');
            const imageCountWarning = imageUploadContainer.querySelector('.image-count-warning');
            const manualImageUpload = imageUploadContainer.querySelector('#manual-image-upload');
            const dragOverlay = imageUploadContainer.querySelector('.drag-overlay');
            const hiddenInput = imageUploadContainer.querySelector('.uploaded-images-data');

            // Track uploaded images
            let uploadedImages = [];
            const MAX_IMAGES = 5;
            const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1MB in bytes

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

                const imageFiles = [];
                // Find image files
                for (let i = 0; i < files.length; i++) {
                    if (files[i].type.indexOf('image/') === 0) {
                        imageFiles.push(files[i]);
                    }
                }

                // Process multiple files
                uploadMultipleImages(imageFiles);
            }

            // Set up drag and drop for the entire container
            imageUploadContainer.addEventListener('dragover', function(e) {
                if (hasImageFile(e.dataTransfer)) {
                    e.preventDefault();
                    e.stopPropagation();
                    dragOverlay.classList.remove('hidden');
                }
            });

            imageUploadContainer.addEventListener('dragleave', function(e) {
                // Only hide if we're leaving the container entirely
                if (!imageUploadContainer.contains(e.relatedTarget)) {
                    dragOverlay.classList.add('hidden');
                }
            });

            imageUploadContainer.addEventListener('drop', function(e) {
                if (hasImageFile(e.dataTransfer)) {
                    e.preventDefault();
                    e.stopPropagation();
                    dragOverlay.classList.add('hidden');
                    handleDroppedFiles(e.dataTransfer.files);
                }
            });

            // Function to add an image to the gallery
            function addImageToGallery(imageSrc, imageId, imageName = null, isExisting = false) {
                const imageContainer = document.createElement('div');
                imageContainer.className = 'relative inline-block';
                imageContainer.dataset.imageId = imageId;

                const image = document.createElement('img');
                image.src = imageSrc;
                image.className = 'max-h-32 rounded-md shadow-sm';
                image.alt = imageName || 'Uploaded image';

                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = '&times;';
                removeBtn.className =
                    'absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-md hover:bg-red-600 transition-colors';
                removeBtn.type = 'button'; // Prevent form submission
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove from array
                    const index = uploadedImages.findIndex(img => img.id === imageId);
                    if (index > -1) {
                        uploadedImages.splice(index, 1);
                    }

                    // Remove from gallery
                    imageContainer.remove();

                    // Update count warning
                    updateImageCountWarning();

                    // Update hidden input value
                    updateImagesValue();
                });

                imageContainer.appendChild(image);
                imageContainer.appendChild(removeBtn);
                imageGallery.appendChild(imageContainer);
            }

            // Function to update image count warning
            function updateImageCountWarning() {
                if (uploadedImages.length >= MAX_IMAGES) {
                    imageCountWarning.classList.remove('hidden');
                } else {
                    imageCountWarning.classList.add('hidden');
                }
            }

            // Function to update the hidden input with all image data
            function updateImagesValue() {
                hiddenInput.value = JSON.stringify(uploadedImages);
            }

            // Handle manual image upload button (now supports multiple files)
            manualImageUpload.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    const filesArray = Array.from(this.files);
                    uploadMultipleImages(filesArray);
                    // Reset the input so the same files can be selected again
                    this.value = '';
                }
            });

            // Function to upload multiple images
            function uploadMultipleImages(files) {
                if (!files || files.length === 0) return;

                // Check if we'll exceed the limit
                const remainingSlots = MAX_IMAGES - uploadedImages.length;
                if (remainingSlots <= 0) {
                    uploadStatus.innerHTML = 'Maksimal 5 gambar sudah tercapai';
                    uploadStatus.className = 'upload-status mt-2 text-sm text-red-500';
                    updateImageCountWarning();
                    return;
                }

                // Limit files to remaining slots
                const filesToUpload = files.slice(0, remainingSlots);

                if (files.length > remainingSlots) {
                    uploadStatus.innerHTML =
                        `Hanya ${remainingSlots} gambar yang dapat diupload (maksimal 5 gambar)`;
                    uploadStatus.className = 'upload-status mt-2 text-sm text-branding-primary';
                }

                // Upload each file
                let uploadedCount = 0;
                let totalFiles = filesToUpload.length;

                uploadStatus.innerHTML = `Mengupload ${totalFiles} gambar...`;
                uploadStatus.className = 'upload-status mt-2 text-sm text-blue-500';

                filesToUpload.forEach((file, index) => {
                    uploadImage(file, () => {
                        uploadedCount++;

                        // Update progress
                        uploadStatus.innerHTML =
                            `Mengupload gambar ${uploadedCount}/${totalFiles}...`;

                        // When all uploads are complete
                        if (uploadedCount === totalFiles) {
                            uploadStatus.innerHTML = `${totalFiles} gambar berhasil diupload!`;
                            uploadStatus.className = 'upload-status mt-2 text-sm text-green-500';

                            // Clear success message after 3 seconds
                            setTimeout(() => {
                                uploadStatus.innerHTML = '';
                            }, 3000);
                        }
                    });
                });
            }

            // Function to handle single image upload
            function uploadImage(file, onSuccess = null) {
                // Check file size
                if (file.size > MAX_FILE_SIZE) {
                    uploadStatus.innerHTML = `Error: File "${file.name}" melebihi 1MB`;
                    uploadStatus.className = 'upload-status mt-2 text-sm text-red-500';
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                fetch('/image/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.location) {
                            // Store image data
                            const imageId = 'img-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                            const imageData = {
                                id: imageId,
                                url: data.location,
                                name: file.name,
                                isNew: true // Mark as new upload
                            };

                            uploadedImages.push(imageData);

                            // Add to gallery
                            addImageToGallery(data.location, imageId, file.name, false);

                            // Update hidden input
                            updateImagesValue();

                            // Update warning
                            updateImageCountWarning();

                            // Call success callback if provided
                            if (onSuccess) {
                                onSuccess();
                            }
                        } else {
                            throw new Error(data.error || 'Upload failed');
                        }
                    })
                    .catch(error => {
                        console.error('Upload error:', error);
                        uploadStatus.innerHTML = `Error upload "${file.name}": ${error.message}`;
                        uploadStatus.className = 'upload-status mt-2 text-sm text-red-500';
                    });
            }

            // Initialize any existing images in the gallery
            if (hiddenInput.value) {
                try {
                    const existingImages = JSON.parse(hiddenInput.value);
                    if (Array.isArray(existingImages)) {
                        existingImages.forEach(img => {
                            // Ensure we have the required fields
                            if (img.url) {
                                // For existing images, preserve their database ID if available
                                const imageData = {
                                    id: img.id || ('existing-' + Date.now() + '-' + Math.random()
                                        .toString(36).substr(2, 9)),
                                    url: img.url,
                                    name: img.name || 'Uploaded image',
                                    isExisting: true
                                };

                                uploadedImages.push(imageData);
                                addImageToGallery(img.url, imageData.id, img.name, true);
                            }
                        });

                        // Update warning
                        updateImageCountWarning();

                        // Update hidden input to ensure consistent format
                        updateImagesValue();
                    }
                } catch (e) {
                    console.error('Error parsing existing images:', e);
                    console.log('Raw hiddenInput.value:', hiddenInput.value);
                }
            }

            // Make functions available globally for backward compatibility
            window.addImageToGallery = addImageToGallery;
            window.updateImagesValue = updateImagesValue;
        });
    </script>
@endpush
