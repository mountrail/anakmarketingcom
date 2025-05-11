{{-- resources/views/components/user-profile.blade.php --}}

@props(['user', 'date'])

<div class="flex items-center space-x-3">
    <div class="relative flex-shrink-0 w-12 h-12">
        @php
            // Determine which profile picture to use based on priority
            $profileImage = null;
            if ($user->profile_picture) {
                $profileImage = asset('storage/' . $user->profile_picture);
            } elseif ($user->avatar) {
                $profileImage = $user->avatar;
            } else {
                $profileImage = asset('storage/uploads/images/portrait.png');
            }
        @endphp
        <img src="{{ $profileImage }}" alt="{{ $user->name }}"
            class="w-full h-full object-cover rounded-full">
    </div>
    <div class="flex flex-col">
        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            @if(isset($user->title) && $user->title)
                <span>{{ $user->title }}</span>
            @endif
            <span>{{ $date->format('j M Y') }}</span>
        </div>
    </div>
</div>
