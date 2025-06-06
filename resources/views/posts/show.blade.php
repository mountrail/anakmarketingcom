{{-- resources/views/posts/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6">
            <div class="bg-white dark:bg-gray-800">
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
                        <div class="prose dark:prose-invert max-w-none min-h-48">
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

                    <!-- Action Bar for both authenticated and non-authenticated users -->
                    <div class="flex items-center justify-end mt-4">
                        <x-action-bar :model="$post" modelType="post" :showVoteScore="false" :showCommentCount="true"
                            :showShare="true" />
                    </div>

                    @auth
                        <!-- Answer form and list only for authenticated users -->

                        <!-- Include answer form partial -->
                        @include('posts.partials.answer-form', ['post' => $post])

                        <!-- Include answers list partial -->
                        @include('posts.partials.answers-list', ['post' => $post])
                    @else
                        <!-- Access restriction message in place of answer/comment area -->
                        <div class="my-8 border-y py-8">
                            <div class="text-center">
                                <div class="text-xl font-bold mb-4">Ups! Akses terbatas</div>
                                <p class="mb-6 text-gray-600 dark:text-gray-400">
                                    Daftar untuk melihat dan terlibat langsung dalam pertanyaan dan diskusi
                                </p>
                                <div class="flex flex-col justify-center items-center">
                                    <a data-auth-action="register" class="flex cursor-pointer">
                                        <div
                                            class="w-max px-4 py-2 mb-6 bg-branding-primary text-branding-light rounded-md text-sm font-medium shadow-md">
                                            Daftar
                                        </div>
                                    </a>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Sudah memiliki akun? <a data-auth-action="login"
                                            class="cursor-pointer text-branding-primary hover:underline">Masuk</a> di sini
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

{{-- NO toast scripts here - all flash messages are handled globally in app.blade.php --}}
