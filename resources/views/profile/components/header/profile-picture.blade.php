{{-- resources\views\profile\components\header\profile-picture.blade.php --}}
@props(['user', 'isOwner'])

<div class="relative inline-block">
    <img src="{{ $user->getProfileImageUrl() }}" alt="{{ $user->name }}"
        class="w-32 h-32 rounded-full mx-auto mb-4 object-cover object-center border-4 border-white shadow-lg"
        style="aspect-ratio: 1/1;">

    @if ($isOwner)
        <x-primary-button onclick="document.getElementById('profile_picture_input').click()" variant="primary"
            size="sm" class="mb-8">
            Upload Foto
        </x-primary-button>
        <input type="file" id="profile_picture_input" name="profile_picture" accept="image/*" class="hidden">
    @endif
</div>
