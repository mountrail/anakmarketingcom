{{-- resources\views\profile\components\header\profile-picture.blade.php --}}
@props(['user', 'isOwner', 'showUploadButton' => false])

<div class="relative inline-block">
    <img src="{{ $user->getProfileImageUrl() }}" alt="{{ $user->name }}"
        class="w-32 h-32 rounded-full mx-auto mb-4 object-cover object-center border-4 border-white shadow-lg"
        style="aspect-ratio: 1/1;" id="profile-image-preview">

    @if ($isOwner && $showUploadButton)
        <form id="profile-picture-form" enctype="multipart/form-data" method="POST"
            action="{{ route('profile.update-profile-picture') }}" class="mb-4">
            @csrf

            <!-- Upload Button with Loading State -->
            <div class="relative flex justify-center">
                <x-primary-button id="upload-photo-btn"
                    onclick="document.getElementById('profile_picture_input').click()" variant="primary" size="sm"
                    type="button"
                    class="mb-2 disabled:bg-essentials-inactive disabled:opacity-100 disabled:cursor-not-allowed">
                    <span id="upload-btn-text">Upload Foto</span>
                    <span id="upload-btn-loading" class="hidden">
                        <span class="inline-flex items-center">
                            <x-loading-spinner size="sm" color="white" />
                            <span class="ml-2">Uploading...</span>
                        </span>
                    </span>
                </x-primary-button>
            </div>

            <input type="file" id="profile_picture_input" name="profile_picture"
                accept="image/jpeg,image/jpg,image/png" class="hidden">
            <div class="text-xs text-gray-500 text-center">JPG / PNG max. 5 MB</div>
        </form>
    @endif
</div>

@if ($isOwner && $showUploadButton)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profilePictureInput = document.getElementById('profile_picture_input');
            const profileImagePreview = document.getElementById('profile-image-preview');
            const uploadBtn = document.getElementById('upload-photo-btn');
            const uploadBtnText = document.getElementById('upload-btn-text');
            const uploadBtnLoading = document.getElementById('upload-btn-loading');
            const form = document.getElementById('profile-picture-form');

            function showUploadLoading() {
                uploadBtn.disabled = true;
                uploadBtnText.classList.add('hidden');
                uploadBtnLoading.classList.remove('hidden');
                uploadBtnLoading.classList.add('inline-flex', 'items-center');
            }

            function hideUploadLoading() {
                uploadBtn.disabled = false;
                uploadBtnText.classList.remove('hidden');
                uploadBtnLoading.classList.add('hidden');
                uploadBtnLoading.classList.remove('inline-flex', 'items-center');
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
                    toast(validationError, 'error', {
                        duration: 6000,
                        position: 'top-right'
                    });
                    profilePictureInput.value = ''; // Clear the input
                    return false;
                }

                // Show loading state
                showUploadLoading();

                // Preview the image immediately
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);

                // Submit form
                form.submit();
            });

            // Handle form submission errors (if page reloads due to validation errors)
            window.addEventListener('load', function() {
                hideUploadLoading();
            });
        });
    </script>
@endif
