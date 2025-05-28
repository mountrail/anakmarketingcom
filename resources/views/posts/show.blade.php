{{-- resources/views/posts/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Post Header Section -->
                    <div class="mb-6">
                        <!-- Category and post time info -->
                        <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span class="mr-2">{{ $post->type === 'question' ? 'Pertanyaan' : 'Diskusi' }}</span>
                            <span class="mx-2">|</span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                            <span class="ml-auto">{{ $post->view_count }} views</span>
                        </div>

                        <!-- Post Title -->
                        <h1 class="text-2xl font-bold mb-4">{{ $post->title }}</h1>

                        <!-- User Profile Section -->
                        <x-user-profile-info :user="$post->user" :timestamp="null" badgeSize="w-7 h-7" profileSize="h-12 w-12"
                            :showJobInfo="true" />
                    </div>

                    <!-- Post Content Section -->
                    <div class="mt-6 border-t pt-6">
                        <!-- Full content for both authenticated and non-authenticated users -->
                        <div class="prose dark:prose-invert max-w-none">
                            {!! clean($post->content) !!}

                            <!-- Image Gallery Display using the PostImage relationship -->
                            @if ($post->images->count() > 0)
                                @php
                                    // Keep as collection instead of converting to array
                                    $images = $post->images->map(function ($image) {
                                        return (object) [
                                            'id' => $image->id,
                                            'url' => $image->url,
                                            'name' => $image->name ?: basename($image->url),
                                            'file_name' => basename($image->url),
                                        ];
                                    });
                                    // Don't call toArray() here - keep it as a collection
                                @endphp
                                @include('posts.partials.image-gallery', ['images' => $images])
                            @endif
                        </div>
                    </div>

                    @auth
                        <!-- Action Bar-->
                        <div class="flex items-center justify-end mt-4">
                            <x-action-bar :model="$post" modelType="post" :showVoteScore="false" :showCommentCount="true"
                                :showShare="true" />
                        </div>

                        <!-- Answer form and list only for authenticated users -->

                        <!-- Include answer form partial -->
                        @include('posts.partials.answer-form', ['post' => $post])

                        <!-- Include answers list partial -->
                        @include('posts.partials.answers-list', ['post' => $post])
                    @else
                        <!-- For non-authenticated users, show actual content with fade effect -->
                        <div class="relative mt-8">
                            <!-- Content preview container with fade effect -->
                            <div class="content-preview-container">
                                <!-- Your preview content would go here -->
                                <div class="preview-content">
                                    <!-- This would contain a preview of the answers/content -->
                                </div>
                            </div>

                            <!-- Fade overlay -->
                            <div class="answers-fade"></div>

                            <!-- Login restriction message -->
                            <div class="login-restriction-container">
                                <div class="text-xl font-bold mb-4">Ups! Akses terbatas</div>
                                <p class="mb-6">Daftar untuk melihat dan terlibat langsung dalam pertanyaan dan diskusi</p>
                                <div class="flex flex-col justify-center items-center">
                                    <a data-auth-action="register" class="flex cursor-pointer">
                                        <div
                                            class="w-max px-4 py-2 mb-6 bg-branding-primary text-branding-light rounded-md text-sm font-medium shadow-md">
                                            Daftar
                                        </div>
                                    </a>
                                    <div class="text-sm">
                                        Sudah memiliki akun? <a data-auth-action="login"
                                            class="cursor-pointer text-branding-primary">Masuk</a> di sini
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .content-preview-container {
            max-height: 300px;
            overflow: hidden;
            position: relative;
        }

        .preview-content {
            padding-bottom: 20px;
        }

        .answers-fade {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 350px;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.5) 10%, rgba(255, 255, 255, 1) 20%);
            pointer-events: none;
            z-index: 10;
        }

        .dark .answers-fade {
            background: linear-gradient(to bottom, rgba(31, 41, 55, 0) 0%, rgba(31, 41, 55, 1) 20%);
        }

        .login-restriction-container {
            position: relative;
            margin-top: -180px;
            padding: 20px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 20;
        }

        .dark .login-restriction-container {
            background-color: rgba(31, 41, 55, 0.9);
        }
    </style>
@endpush

{{-- NO toast scripts here - all flash messages are handled globally in app.blade.php --}}
