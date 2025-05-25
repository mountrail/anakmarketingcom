{{-- resources/views/profile/components/header/user-info.blade.php --}}
@props(['user', 'isOwner'])

@if (!$isOwner)
    <h1 class="font-bold text-branding-black dark:text-white">{{ $user->name }}</h1>
    @if ($user->job_title || $user->company)
        <p class="text-xl text-branding-black dark:text-gray-400 mb-4">
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
@endif
