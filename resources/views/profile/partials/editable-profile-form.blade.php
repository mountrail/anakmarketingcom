{{-- resources/views/profile/partials/editable-profile-form.blade.php --}}
@props(['user', 'errors'])

<form method="POST" action="{{ route('profile.update-profile') }}" enctype="multipart/form-data" class="space-y-6"
    id="profile-form">
    @csrf
    @method('PATCH')

    <!-- Hidden file input for profile picture -->
    <input type="file" name="profile_picture" id="hidden_profile_picture" accept="image/*" class="hidden">

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
        <x-textarea id="bio" name="bio" class="mt-1 block w-full bg-essentials-inactive bg-opacity-20"
            rows="4"
            placeholder="Ceritakan lebih detail profil atau keahlian Anda!">{{ old('bio', $user->bio) }}</x-textarea>
        <x-input-error class="mt-2" :messages="$errors->get('bio')" />
    </div>

    <!-- Save Button -->
    <div class="flex justify-center">
        <button type="submit" id="save-button"
            class="bg-branding-primary text-white shadow-md px-8 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
            disabled>
            Simpan
        </button>
    </div>
</form>
