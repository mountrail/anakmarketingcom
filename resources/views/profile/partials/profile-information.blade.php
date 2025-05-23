{{-- resources/views/profile/partials/profile-information.blade.php --}}
@props(['user', 'isOwner', 'errors'])

<div class="mb-20">
    @if ($isOwner)
        @include('profile.partials.editable-profile-form', [
            'user' => $user,
            'errors' => $errors,
        ])
    @else
        @include('profile.partials.view-only-profile', [
            'user' => $user,
        ])
    @endif
</div>
