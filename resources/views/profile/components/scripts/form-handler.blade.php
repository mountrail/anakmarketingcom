{{-- resources/views/profile/components/scripts/form-handler.blade.php --}}
@props(['user'])

<script>
    let hasUnsavedBasicInfoChanges = false;
    let hasUnsavedBioChanges = false;
    let originalBasicInfoData = {};
    let originalBioData = {};
    let isBasicInfoFormSubmitting = false;
    let isBioFormSubmitting = false;

    // Store original basic info data
    function storeOriginalBasicInfoData() {
        const form = document.getElementById('basic-info-form');
        if (!form) return;

        const formData = new FormData(form);
        originalBasicInfoData = {};

        // Store text inputs
        for (let [key, value] of formData.entries()) {
            if (key !== 'profile_picture' && key !== '_token' && key !== '_method') {
                originalBasicInfoData[key] = value;
            }
        }

        // Store current profile picture src
        const profileImg = document.querySelector('img[alt*="{{ $user->name ?? '' }}"]');
        if (profileImg) {
            originalBasicInfoData['current_profile_picture'] = profileImg.src;
        }
    }

    // Store original bio data
    function storeOriginalBioData() {
        const bioTextarea = document.getElementById('bio');
        if (bioTextarea) {
            originalBioData['bio'] = bioTextarea.value;
        }
    }

    // Check if basic info has changed
    function checkBasicInfoChanges() {
        const form = document.getElementById('basic-info-form');
        if (!form) return false;

        const currentFormData = new FormData(form);
        let hasChanges = false;

        // Check text inputs
        for (let [key, value] of currentFormData.entries()) {
            if (key !== 'profile_picture' && key !== '_token' && key !== '_method') {
                if (originalBasicInfoData[key] !== value) {
                    hasChanges = true;
                    break;
                }
            }
        }

        // Check profile picture
        const profileImg = document.querySelector('img[alt*="{{ $user->name ?? '' }}"]');
        if (profileImg && originalBasicInfoData['current_profile_picture'] !== profileImg.src) {
            hasChanges = true;
        }

        // Check if profile picture file is selected
        const fileInput = document.getElementById('hidden_profile_picture');
        if (fileInput && fileInput.files.length > 0) {
            hasChanges = true;
        }

        hasUnsavedBasicInfoChanges = hasChanges;

        // Enable/disable save button
        const saveButton = document.getElementById('save-basic-info-button');
        if (saveButton) {
            updateButtonState(saveButton, hasChanges);
        }

        return hasChanges;
    }

    // Check if bio has changed
    function checkBioChanges() {
        const bioTextarea = document.getElementById('bio');
        if (!bioTextarea) return false;

        const hasChanges = originalBioData['bio'] !== bioTextarea.value;
        hasUnsavedBioChanges = hasChanges;

        // Enable/disable save button
        const saveButton = document.getElementById('save-bio-button');
        if (saveButton) {
            updateButtonState(saveButton, hasChanges);
        }

        return hasChanges;
    }

    // Update button state (enable/disable with visual feedback)
    function updateButtonState(button, hasChanges) {
        button.disabled = !hasChanges;

        if (hasChanges) {
            button.classList.remove('bg-essentials-inactive', 'cursor-not-allowed');
            button.classList.add('bg-branding-primary', 'hover:bg-opacity-90');
        } else {
            button.classList.remove('bg-branding-primary', 'hover:bg-opacity-90');
            button.classList.add('bg-essentials-inactive', 'cursor-not-allowed');
        }
    }

    // Initialize form change detection
    document.addEventListener('DOMContentLoaded', function() {
        // Store original data when page loads
        storeOriginalBasicInfoData();
        storeOriginalBioData();

        // Add event listeners to basic info form
        const basicInfoForm = document.getElementById('basic-info-form');
        if (basicInfoForm) {
            basicInfoForm.addEventListener('input', checkBasicInfoChanges);
            basicInfoForm.addEventListener('change', checkBasicInfoChanges);

            // Handle form submission
            basicInfoForm.addEventListener('submit', function() {
                isBasicInfoFormSubmitting = true;
            });
        }

        // Add event listeners to bio form
        const bioForm = document.getElementById('bio-form');
        if (bioForm) {
            bioForm.addEventListener('input', checkBioChanges);
            bioForm.addEventListener('change', checkBioChanges);

            // Handle form submission
            bioForm.addEventListener('submit', function() {
                isBioFormSubmitting = true;
            });
        }

        // Handle profile picture upload
        const profilePictureInput = document.getElementById('profile_picture_input');
        const hiddenProfilePictureInput = document.getElementById('hidden_profile_picture');

        if (profilePictureInput && hiddenProfilePictureInput) {
            profilePictureInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Copy the file to the hidden input (for form submission)
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    hiddenProfilePictureInput.files = dataTransfer.files;

                    // Preview the image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const profileImg = document.querySelector(
                            'img[alt*="{{ $user->name ?? '' }}"]');
                        if (profileImg) {
                            profileImg.src = e.target.result;
                        }
                        checkBasicInfoChanges();
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // Warn user about unsaved changes when leaving page
    window.addEventListener('beforeunload', function(e) {
        if ((hasUnsavedBasicInfoChanges && !isBasicInfoFormSubmitting) ||
            (hasUnsavedBioChanges && !isBioFormSubmitting)) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
