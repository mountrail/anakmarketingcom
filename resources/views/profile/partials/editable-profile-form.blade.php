{{-- resources/views/profile/partials/editable-profile-form.blade.php --}}
@props(['user', 'errors'])

<!-- Profile Basic Info Form (Name, Job, Company) -->
<form method="POST" action="{{ route('profile.update-basic-info') }}" class="space-y-6" id="basic-info-form">
    @csrf
    @method('PATCH')

    <!-- Name -->
    <div>
        <x-input-label for="name" :value="__('Nama')" />
        <x-text-input id="name" name="name" type="text"
            class="mt-1 block w-full bg-essentials-inactive bg-opacity-20" :value="old('name', $user->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <!-- Job Title -->
    <div>
        <x-input-label for="job_title" :value="__('Pekerjaan')" />
        <x-text-input id="job_title" name="job_title" type="text"
            class="mt-1 block w-full bg-essentials-inactive bg-opacity-20" :value="old('job_title', $user->job_title)"
            placeholder="contoh: Performance Marketing" required />
        <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
    </div>

    <!-- Company -->
    <div>
        <x-input-label for="company" :value="__('Perusahaan (opsional)')" />
        <x-text-input id="company" name="company" type="text"
            class="mt-1 block w-full bg-essentials-inactive bg-opacity-20" :value="old('company', $user->company)"
            placeholder="contoh: Apple Computer" />
        <x-input-error class="mt-2" :messages="$errors->get('company')" />
    </div>

    <!-- Save Basic Info Button -->
    <div class="flex justify-center">
        <x-primary-button type="submit" id="save-basic-info-button" size="xl"
            class="disabled:bg-essentials-inactive disabled:opacity-100 disabled:cursor-not-allowed transition-all duration-200">
            <span class="button-text">Simpan</span>
            <span class="loading-spinner hidden">
                <span class="inline-flex items-center">
                    <x-loading-spinner size="sm" color="white" />
                    <span class="ml-2">Menyimpan...</span>
                </span>
            </span>
        </x-primary-button>
    </div>
</form>

<!-- Bio/Description Form -->
<form method="POST" action="{{ route('profile.update-bio') }}" class="space-y-6" id="bio-form">
    @csrf
    @method('PATCH')

    <!-- Bio/Description -->
    <div>
        <x-input-label for="bio" :value="__('Deskripsi')" />
        <x-textarea id="bio" name="bio" class="mt-1 block w-full bg-essentials-inactive bg-opacity-20"
            rows="4"
            placeholder="Ceritakan lebih detail profil atau keahlian Anda!">{{ old('bio', $user->bio) }}</x-textarea>
        <div class="mt-1 text-sm text-gray-500">
            <span id="word-count">0</span>/250 kata
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('bio')" />
    </div>

    <!-- Save Bio Button -->
    <div class="flex justify-center">
        <x-primary-button type="submit" id="save-bio-button" size="xl"
            class="disabled:bg-essentials-inactive disabled:opacity-100 disabled:cursor-not-allowed transition-all duration-200">
            <span class="button-text">Simpan</span>
            <span class="loading-spinner hidden">
                <span class="inline-flex items-center">
                    <x-loading-spinner size="sm" color="white" />
                    <span class="ml-2">Menyimpan...</span>
                </span>
            </span>
        </x-primary-button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements
        const basicInfoForm = document.getElementById('basic-info-form');
        const bioForm = document.getElementById('bio-form');
        const basicInfoButton = document.getElementById('save-basic-info-button');
        const bioButton = document.getElementById('save-bio-button');

        // Track form changes to enable/disable buttons
        let basicInfoChanged = false;
        let bioChanged = false;

        // Store original form values
        const originalBasicInfo = {
            name: document.getElementById('name').value,
            job_title: document.getElementById('job_title').value,
            company: document.getElementById('company').value
        };
        const originalBio = document.getElementById('bio').value;

        // Function to check if required fields are filled
        function areRequiredFieldsFilled() {
            const nameValue = document.getElementById('name').value.trim();
            const jobTitleValue = document.getElementById('job_title').value.trim();
            return nameValue !== '' && jobTitleValue !== '';
        }

        // Function to update button state and color
        function updateButtonState(button, hasChanges, isBasicInfoForm = false) {
            // For basic info form, also check if required fields are filled
            const canSubmit = isBasicInfoForm ? (hasChanges && areRequiredFieldsFilled()) : hasChanges;

            if (canSubmit) {
                button.disabled = false;
                // Remove disabled styles and add active brand-primary color
                button.classList.remove('opacity-50', 'cursor-not-allowed');
                button.classList.add('bg-branding-primary', 'hover:bg-opacity-90', 'focus:bg-opacity-90');
            } else {
                button.disabled = true;
                // Add disabled styles and remove active colors
                button.classList.add('opacity-50', 'cursor-not-allowed');
                button.classList.remove('bg-branding-primary', 'hover:bg-opacity-90', 'focus:bg-opacity-90');
            }
        }

        const bioField = document.getElementById('bio');
        const wordCountDisplay = document.getElementById('word-count');

        function updateWordCount() {
            const text = bioField.value.trim();
            const wordCount = text === '' ? 0 : text.split(/\s+/).length;

            if (wordCountDisplay) {
                wordCountDisplay.textContent = wordCount;

                // Change color if approaching limit
                if (wordCount > 250) {
                    wordCountDisplay.classList.add('text-red-500');
                    wordCountDisplay.classList.remove('text-gray-500');
                } else if (wordCount > 230) {
                    wordCountDisplay.classList.add('text-yellow-500');
                    wordCountDisplay.classList.remove('text-gray-500', 'text-red-500');
                } else {
                    wordCountDisplay.classList.add('text-gray-500');
                    wordCountDisplay.classList.remove('text-yellow-500', 'text-red-500');
                }
            }
        }

        if (bioField) {
            bioField.addEventListener('input', function() {
                checkBioChanges();
                updateWordCount();
            });
            bioField.addEventListener('change', checkBioChanges);
            // Initial word count
            updateWordCount();
        }
        // Check for changes in basic info form
        function checkBasicInfoChanges() {
            const currentValues = {
                name: document.getElementById('name').value,
                job_title: document.getElementById('job_title').value,
                company: document.getElementById('company').value
            };

            basicInfoChanged = JSON.stringify(currentValues) !== JSON.stringify(originalBasicInfo);
            updateButtonState(basicInfoButton, basicInfoChanged, true);
        }

        // Check for changes in bio form
        function checkBioChanges() {
            bioChanged = document.getElementById('bio').value !== originalBio;
            updateButtonState(bioButton, bioChanged);
        }

        // Add event listeners for form changes
        ['name', 'job_title', 'company'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', checkBasicInfoChanges);
                field.addEventListener('change', checkBasicInfoChanges);
            }
        });

        // Show loading state on form submission
        function showLoadingState(button) {
            const buttonText = button.querySelector('.button-text');
            const loadingSpinner = button.querySelector('.loading-spinner');

            if (buttonText) buttonText.classList.add('hidden');
            if (loadingSpinner) {
                loadingSpinner.classList.remove('hidden');
                loadingSpinner.classList.add('inline-flex', 'items-center');
            }

            button.disabled = true;
        }

        // Add form submission handlers for loading states
        if (basicInfoForm) {
            basicInfoForm.addEventListener('submit', function(e) {
                // Prevent submission if required fields are not filled or no changes
                if (!basicInfoChanged || !areRequiredFieldsFilled()) {
                    e.preventDefault();

                    // Show error message if required fields are missing
                    if (!areRequiredFieldsFilled()) {
                        // You can use your toast system here
                        if (typeof toast === 'function') {
                            toast('Nama dan Pekerjaan harus diisi!', 'error', {
                                duration: 4000,
                                position: 'top-right'
                            });
                        } else {
                            alert('Nama dan Pekerjaan harus diisi!');
                        }
                    }
                    return;
                }

                showLoadingState(basicInfoButton);
            });
        }

        if (bioForm) {
            bioForm.addEventListener('submit', function(e) {
                if (bioChanged) {
                    showLoadingState(bioButton);
                } else {
                    e.preventDefault();
                }
            });
        }

        // Initial state check
        checkBasicInfoChanges();
        checkBioChanges();
    });
</script>
