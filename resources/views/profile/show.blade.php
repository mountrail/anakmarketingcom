{{-- resources/views/profile/show.blade.php --}}

<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Profile Header -->
            <div class="text-center mb-8">
                <div class="relative inline-block">
                    <img src="{{ $user->getProfileImageUrl() }}" alt="{{ $user->name }}"
                        class="w-32 h-32 rounded-full mx-auto mb-4 object-cover object-center border-4 border-white shadow-lg"
                        style="aspect-ratio: 1/1;">

                    @if ($isOwner)
                        <label for="profile_picture_input"
                            class=" bg-branding-primary text-white rounded-lg px-3 py-1 text-sm shadow-lg hover:bg-opacity-90 transition-colors cursor-pointer">
                            Upload Foto
                        </label>
                        <input type="file" id="profile_picture_input" name="profile_picture" accept="image/*"
                            class="hidden">
                    @endif
                </div>

                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $user->name }}</h1>

                @if ($user->job_title || $user->company)
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">
                        @if ($user->job_title)
                            {{ $user->job_title }}
                        @endif
                        @if ($user->job_title && $user->company)
                            |
                        @endif
                        @if ($user->company)
                            {{ $user->company }}
                        @endif
                    </p>
                @endif

                @if (!$isOwner)
                    <button
                        class="bg-branding-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors">
                        Follow
                    </button>
                @endif
            </div>

            <div>
                <!-- Profile Information -->
                <div class="mb-8">
                    @if ($isOwner)
                        <!-- Editable Profile Form -->
                        <form method="POST" action="{{ route('profile.update-profile') }}"
                            enctype="multipart/form-data" class="space-y-6" id="profile-form">
                            @csrf
                            @method('PATCH')

                            <!-- Hidden file input for profile picture -->
                            <input type="file" name="profile_picture" id="hidden_profile_picture" accept="image/*"
                                class="hidden">

                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Nama')" />
                                <x-text-input id="name" name="name" type="text"
                                    class="mt-1 block w-full bg-essentials-inactive bg-opacity-20" :value="old('name', $user->name)"
                                    required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <!-- Job Title -->
                            <div>
                                <x-input-label for="job_title" :value="__('Pekerjaan')" />
                                <x-text-input id="job_title" name="job_title" type="text"
                                    class="mt-1 block w-full bg-essentials-inactive bg-opacity-20" :value="old('job_title', $user->job_title)"
                                    placeholder="contoh: Performance Marketing" />
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

                            <!-- Bio/Description -->
                            <div>
                                <x-input-label for="bio" :value="__('Deskripsi')" />
                                <x-textarea id="bio" name="bio"
                                    class="mt-1 block w-full bg-essentials-inactive bg-opacity-20" rows="4"
                                    placeholder="Ceritakan lebih detail profil atau keahlian Anda!">{{ old('bio', $user->bio) }}</x-textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('bio')" />
                            </div>

                            <!-- Save Button -->
                            <div class="flex justify-center">
                                <button type="submit" id="save-button"
                                    class="bg-branding-primary text-white px-8 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
                                    disabled>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    @else
                        <!-- View-only Profile Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Profil</h2>

                            @if ($user->bio)
                                <div class="mb-6">
                                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $user->bio }}</p>
                                </div>
                            @else
                                <div class="mb-6">
                                    <p class="text-gray-500 dark:text-gray-400">Pengguna belum menambahkan deskripsi
                                        profil.</p>
                                </div>
                            @endif

                            @if ($user->job_title || $user->company)
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    @if ($user->job_title)
                                        <p><strong>Pekerjaan:</strong> {{ $user->job_title }}</p>
                                    @endif
                                    @if ($user->company)
                                        <p><strong>Perusahaan:</strong> {{ $user->company }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Badges Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 text-center">Badges</h3>

                    @if ($isOwner)
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
                            Pilih 3 badge untuk ditampilkan di profil Anda
                        </p>
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
                            Badge yang dipilih oleh {{ $user->name }}
                        </p>
                    @endif

                    <!-- Empty badges container for now -->
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        <p>Belum ada badge yang ditampilkan</p>
                    </div>

                    @if ($isOwner)
                        <div class="text-center">
                            <button
                                class="bg-branding-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-opacity-90 transition-colors">
                                Simpan
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($isOwner)
        <script>
            let hasUnsavedChanges = false;
            let originalFormData = {};

            // Store original form data
            function storeOriginalData() {
                const form = document.getElementById('profile-form');
                const formData = new FormData(form);
                originalFormData = {};

                // Store text inputs
                for (let [key, value] of formData.entries()) {
                    if (key !== 'profile_picture' && key !== '_token' && key !== '_method') {
                        originalFormData[key] = value;
                    }
                }

                // Store current profile picture src
                originalFormData['current_profile_picture'] = document.querySelector('img[alt="{{ $user->name }}"]').src;
            }

            // Check if form data has changed
            function checkForChanges() {
                const form = document.getElementById('profile-form');
                const currentFormData = new FormData(form);
                let hasChanges = false;

                // Check text inputs
                for (let [key, value] of currentFormData.entries()) {
                    if (key !== 'profile_picture' && key !== '_token' && key !== '_method') {
                        if (originalFormData[key] !== value) {
                            hasChanges = true;
                            break;
                        }
                    }
                }

                // Check profile picture
                const currentProfilePicture = document.querySelector('img[alt="{{ $user->name }}"]').src;
                if (originalFormData['current_profile_picture'] !== currentProfilePicture) {
                    hasChanges = true;
                }

                // Check if profile picture file is selected
                const fileInput = document.getElementById('hidden_profile_picture');
                if (fileInput.files.length > 0) {
                    hasChanges = true;
                }

                hasUnsavedChanges = hasChanges;

                // Enable/disable save button
                const saveButton = document.getElementById('save-button');
                saveButton.disabled = !hasChanges;

                return hasChanges;
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                storeOriginalData();

                // Add event listeners to form inputs
                const form = document.getElementById('profile-form');
                const inputs = form.querySelectorAll('input[type="text"], textarea');

                inputs.forEach(input => {
                    input.addEventListener('input', checkForChanges);
                    input.addEventListener('change', checkForChanges);
                });

                // Handle form submission
                form.addEventListener('submit', function() {
                    hasUnsavedChanges = false;
                });
            });

            // Handle profile picture upload button
            document.getElementById('profile_picture_input').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Copy the file to the hidden input in the form
                    const hiddenInput = document.getElementById('hidden_profile_picture');
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    hiddenInput.files = dt.files;

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.querySelector('img[alt="{{ $user->name }}"]').src = e.target.result;
                        checkForChanges(); // Check for changes after image preview
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Warn before leaving page with unsaved changes
            window.addEventListener('beforeunload', function(e) {
                if (hasUnsavedChanges) {
                    const message =
                        'Anda memiliki perubahan yang belum disimpan. Apakah Anda yakin ingin meninggalkan halaman ini?';
                    e.preventDefault();
                    e.returnValue = message;
                    return message;
                }
            });

            // Handle navigation within the site
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && hasUnsavedChanges && !link.href.includes('#')) {
                    if (!confirm(
                            'Anda memiliki perubahan yang belum disimpan. Apakah Anda yakin ingin meninggalkan halaman ini?'
                            )) {
                        e.preventDefault();
                    }
                }
            });
        </script>
    @endif
</x-app-layout>
