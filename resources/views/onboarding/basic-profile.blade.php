{{-- resources\views\onboarding\basic-profile.blade.php --}}
@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-branding-primary mb-2">
                Karena tak kenal maka tak sayang,<br>
                yuk isi profilmu sekarang!
            </h1>
            <p class="text-gray-700 font-medium">
                Kesempatan personal branding, nih!
            </p>
        </div>

        <!-- Profile Form -->
        <form method="POST" action="{{ route('onboarding.update-basic-profile') }}" enctype="multipart/form-data"
            class="space-y-6">
            @csrf

            <!-- Profile Picture Section -->
            <div class="text-center">
                <div class="relative inline-block mb-4">
                    <img src="{{ $user->getProfileImageUrl() }}" alt="{{ $user->name }}"
                        class="w-32 h-32 rounded-full mx-auto object-cover object-center border-4 border-white shadow-lg"
                        style="aspect-ratio: 1/1;" id="profile-image-preview">
                </div>

                <!-- Upload Button -->
                <div class="mb-4">
                    <x-primary-button id="upload-photo-btn"
                        onclick="document.getElementById('profile_picture_input').click()" variant="primary" size="sm"
                        type="button"
                        class="disabled:bg-essentials-inactive disabled:opacity-100 disabled:cursor-not-allowed">
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

                @error('profile_picture')
                    <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                @enderror
            </div>

            <!-- Name Field -->
            <div>
                <x-input-label for="name" :value="__('Nama')" />
                <x-text-input id="name" name="name" type="text"
                    class="mt-1 block w-full bg-essentials-inactive bg-opacity-20" :value="old('name', $user->name)" required autofocus
                    placeholder="Nama" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Job Title Field -->
            <div>
                <x-input-label for="job_title" :value="__('Pekerjaan')" />
                <x-text-input id="job_title" name="job_title" type="text"
                    class="mt-1 block w-full bg-essentials-inactive bg-opacity-20" :value="old('job_title', $user->job_title)"
                    placeholder="contoh: Performance Marketing" />
                <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
            </div>

            <!-- Company Field -->
            <div>
                <x-input-label for="company" :value="__('Perusahaan (opsional)')" />
                <x-text-input id="company" name="company" type="text"
                    class="mt-1 block w-full bg-essentials-inactive bg-opacity-20" :value="old('company', $user->company)"
                    placeholder="contoh: Apple Computer" />
                <x-input-error class="mt-2" :messages="$errors->get('company')" />
            </div>

            <!-- General Error Display -->
            @if ($errors->has('error'))
                <div class="text-red-500 text-sm text-center mt-4">
                    {{ $errors->first('error') }}
                </div>
            @endif

            <!-- Submit Button -->
            <div class="flex justify-center pt-4">
                <x-primary-button type="submit" id="save-profile-button" size="xl"
                    class="w-auto px-8 disabled:bg-essentials-inactive disabled:opacity-100 disabled:cursor-not-allowed transition-all duration-200">
                    <span class="button-text">Lanjutkan</span>
                    <span class="loading-spinner hidden">
                        <span class="inline-flex items-center">
                            <x-loading-spinner size="sm" color="white" />
                            <span class="ml-2">Menyimpan...</span>
                        </span>
                    </span>
                </x-primary-button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profilePictureInput = document.getElementById('profile_picture_input');
            const profileImagePreview = document.getElementById('profile-image-preview');
            const uploadBtn = document.getElementById('upload-photo-btn');
            const uploadBtnText = document.getElementById('upload-btn-text');
            const uploadBtnLoading = document.getElementById('upload-btn-loading');
            const form = document.querySelector('form');
            const saveButton = document.getElementById('save-profile-button');
            const nameInput = document.getElementById('name');
            const jobTitleInput = document.getElementById('job_title');

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

            function showSaveLoading() {
                const buttonText = saveButton.querySelector('.button-text');
                const loadingSpinner = saveButton.querySelector('.loading-spinner');

                if (buttonText) buttonText.classList.add('hidden');
                if (loadingSpinner) {
                    loadingSpinner.classList.remove('hidden');
                    loadingSpinner.classList.add('inline-flex', 'items-center');
                }

                saveButton.disabled = true;
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

            function validateRequiredFields() {
                const name = nameInput.value.trim();
                const jobTitle = jobTitleInput.value.trim();

                return name !== '' && jobTitle !== '';
            }

            function updateSubmitButton() {
                const isValid = validateRequiredFields();
                saveButton.disabled = !isValid;

                if (isValid) {
                    saveButton.classList.remove('disabled:bg-essentials-inactive', 'disabled:opacity-100',
                        'disabled:cursor-not-allowed');
                } else {
                    saveButton.classList.add('disabled:bg-essentials-inactive', 'disabled:opacity-100',
                        'disabled:cursor-not-allowed');
                }
            }

            // Handle profile picture selection
            profilePictureInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Validate file
                const validationError = validateFile(file);
                if (validationError) {
                    // Show error (you can customize this based on your toast/alert system)
                    alert(validationError);
                    profilePictureInput.value = ''; // Clear the input
                    return false;
                }

                // Preview the image immediately
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });

            // Add event listeners for required field validation
            nameInput.addEventListener('input', updateSubmitButton);
            jobTitleInput.addEventListener('input', updateSubmitButton);

            // Handle form submission
            form.addEventListener('submit', function(e) {
                if (!validateRequiredFields()) {
                    e.preventDefault();
                    alert('Nama dan Pekerjaan harus diisi untuk melanjutkan.');
                    return false;
                }
                showSaveLoading();
            });

            // Handle page load (in case of validation errors)
            window.addEventListener('load', function() {
                hideUploadLoading();
                updateSubmitButton(); // Check initial state
            });

            // Initial validation check
            updateSubmitButton();
        });
    </script>
@endsection
