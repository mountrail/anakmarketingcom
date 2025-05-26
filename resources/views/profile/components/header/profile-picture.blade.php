{{-- resources\views\profile\components\header\profile-picture.blade.php --}}
@props(['user', 'isOwner'])

<div class="relative inline-block">
    <img src="{{ $user->getProfileImageUrl() }}" alt="{{ $user->name }}"
        class="w-32 h-32 rounded-full mx-auto mb-4 object-cover object-center border-4 border-white shadow-lg"
        style="aspect-ratio: 1/1;" id="profile-image-preview">

    @if ($isOwner)
        <form id="profile-picture-form" enctype="multipart/form-data" class="mb-4">
            @csrf
            <x-primary-button onclick="document.getElementById('profile_picture_input').click()" variant="primary"
                size="sm" type="button" class="mb-2">
                Upload Foto
            </x-primary-button>
            <input type="file" id="profile_picture_input" name="profile_picture"
                accept="image/jpeg,image/jpg,image/png" class="hidden">
            <div class="text-xs text-gray-500 text-center">JPG / PNG max. 5 MB</div>
        </form>

        <!-- Loading indicator -->
        <div id="upload-loading" class="hidden text-center mb-2">
            <div class="inline-flex items-center px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-full">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Mengupload...
            </div>
        </div>

        <!-- Success message -->
        <div id="upload-success" class="hidden text-center mb-2">
            <div class="inline-flex items-center px-3 py-1 text-sm text-green-600 bg-green-100 rounded-full">
                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                Foto berhasil diubah
            </div>
        </div>

        <!-- Error message -->
        <div id="upload-error" class="hidden text-center mb-2">
            <div class="inline-flex items-center px-3 py-1 text-sm text-red-600 bg-red-100 rounded-full">
                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"></path>
                </svg>
                <span id="upload-error-message">Foto lebih dari 5 MB</span>
            </div>
        </div>
    @endif
</div>

@if ($isOwner)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profilePictureInput = document.getElementById('profile_picture_input');
            const profileImagePreview = document.getElementById('profile-image-preview');
            const uploadLoading = document.getElementById('upload-loading');
            const uploadSuccess = document.getElementById('upload-success');
            const uploadError = document.getElementById('upload-error');
            const uploadErrorMessage = document.getElementById('upload-error-message');

            function hideAllMessages() {
                uploadLoading.classList.add('hidden');
                uploadSuccess.classList.add('hidden');
                uploadError.classList.add('hidden');
            }

            function showMessage(messageElement, duration = 3000) {
                hideAllMessages();
                messageElement.classList.remove('hidden');

                if (duration > 0) {
                    setTimeout(() => {
                        messageElement.classList.add('hidden');
                    }, duration);
                }
            }

            function validateFile(file) {
                // Check file size (5MB = 5 * 1024 * 1024 bytes)
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    return 'Foto lebih dari 5 MB';
                }

                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    return 'Format file harus JPG atau PNG';
                }

                return null;
            }

            profilePictureInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Validate file
                const validationError = validateFile(file);
                if (validationError) {
                    uploadErrorMessage.textContent = validationError;
                    showMessage(uploadError);
                    profilePictureInput.value = ''; // Clear the input
                    return;
                }

                // Show loading
                showMessage(uploadLoading, 0);

                // Preview the image immediately
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);

                // Upload the file
                const formData = new FormData();
                formData.append('profile_picture', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'));

                fetch('{{ route('profile.update-profile-picture') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideAllMessages();

                        if (data.success) {
                            // Update the image source with the new URL
                            if (data.profile_image_url) {
                                profileImagePreview.src = data.profile_image_url;
                            }
                            showMessage(uploadSuccess);
                        } else {
                            uploadErrorMessage.textContent = data.message ||
                                'Terjadi kesalahan saat mengupload foto';
                            showMessage(uploadError);
                            // Revert the preview if upload failed
                            profileImagePreview.src = '{{ $user->getProfileImageUrl() }}';
                        }
                    })
                    .catch(error => {
                        console.error('Upload error:', error);
                        hideAllMessages();
                        uploadErrorMessage.textContent = 'Terjadi kesalahan saat mengupload foto';
                        showMessage(uploadError);
                        // Revert the preview if upload failed
                        profileImagePreview.src = '{{ $user->getProfileImageUrl() }}';
                    })
                    .finally(() => {
                        // Clear the input so the same file can be selected again if needed
                        profilePictureInput.value = '';
                    });
            });
        });
    </script>
@endif
