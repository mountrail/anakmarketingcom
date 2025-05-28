{{-- resources/views/profile/partials/profile-scripts.blade.php --}}

{{-- Form handling scripts --}}
@include('profile.components.scripts.form-handler', [
    'user' => $user ?? null,
])

{{-- Posts loading scripts --}}
@include('profile.components.scripts.posts-loader', [
    'user' => $user ?? null,
])
