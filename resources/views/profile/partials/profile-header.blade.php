{{-- resources/views/profile/partials/profile-header.blade.php --}}
@props(['user', 'isOwner'])

<div class="text-center mb-20">
    <div class="relative inline-block">
        <img src="{{ $user->getProfileImageUrl() }}" alt="{{ $user->name }}"
            class="w-32 h-32 rounded-full mx-auto mb-4 object-cover object-center border-4 border-white shadow-lg"
            style="aspect-ratio: 1/1;">

        @if ($isOwner)
            <label for="profile_picture_input"
                class=" bg-branding-primary text-white shadow-md rounded-lg px-3 py-1 text-sm hover:bg-opacity-90 transition-colors cursor-pointer">
                Upload Foto
            </label>
            <input type="file" id="profile_picture_input" name="profile_picture" accept="image/*" class="hidden">
        @endif
    </div>

    <h1 class="text-2xl font-bold text-branding-black dark:text-white">{{ $user->name }}</h1>

    @if ($user->job_title || $user->company)
        <p class="text-lg text-branding-black dark:text-gray-400 mb-4">
            @if ($user->job_title)
                {{ $user->job_title }}
            @endif
            @if ($user->job_title && $user->company)
                <br> at
            @endif
            @if ($user->company)
                {{ $user->company }}
            @endif
        </p>
    @endif

    @if (!$isOwner)
        <button
            class="bg-branding-primary text-white shadow-md px-6 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors">
            Follow
        </button>
    @endif
</div>
