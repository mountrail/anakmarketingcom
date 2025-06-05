{{-- resources/views/profile/edit.blade.php --}}

@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="min-h-screen bg-white dark:bg-brandtext-branding-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Header with Back Button --}}
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.show', auth()->user()) }}"
                        class="inline-flex items-center text-branding-primary hover:text-opacity-80 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke Profil
                    </a>
                </div>
            </div>

            {{-- Profile Picture Section --}}
            <div class="mb-12">
                <div class="flex justify-center">
                    @include('profile.components.header.profile-picture', [
                        'user' => auth()->user(),
                        'isOwner' => true,
                        'showUploadButton' => true,
                    ])
                </div>
            </div>

            {{-- Profile Information Section --}}
            <div class="mb-12">
                <h2
                    class="text-xl font-semibold text-branding-black dark:text-white text-center border-b border-gray-200 dark:border-gray-600 mb-6 pb-2">
                    Informasi Profil
                </h2>
                @include('profile.partials.editable-profile-form', [
                    'user' => auth()->user(),
                    'errors' => $errors,
                ])
            </div>

            {{-- Badges Section --}}
            <div class="mb-12">
                @include('profile.partials.badges-section', [
                    'user' => auth()->user(),
                    'isOwner' => true,
                ])
            </div>
        </div>
    </div>

    {{-- Include form handler for toast notifications --}}
    @include('profile.components.scripts.form-handler')

    {{-- Include scripts for profile edit functionality --}}
@endsection
