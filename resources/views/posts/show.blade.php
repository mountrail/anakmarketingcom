@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Post Header Section -->
                    <div class="mb-6">
                        <!-- Category and post time info -->
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <span class="mr-2">{{ $post->type === 'question' ? 'Pertanyaan' : 'Diskusi' }}</span>
                            <span class="mx-2">|</span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                            <span class="ml-auto">{{ $post->view_count }} views</span>
                        </div>

                        <!-- Post Title -->
                        <h1 class="text-2xl font-bold mb-4">{{ $post->title }}</h1>

                        <!-- User Profile Section -->
                        <div class="flex items-center">
                            <div class="flex items-center">
                                <img src="{{ $post->user->profile_photo_url }}" alt="{{ $post->user->name }}"
                                    class="h-12 w-12 rounded-full object-cover mr-3">
                                <div>
                                    <div class="font-medium">{{ $post->user->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $post->user->description }}
                                    </div>
                                </div>
                            </div>

                            <!-- User Badges -->
                            <div class="ml-auto flex items-center space-x-2">
                                <span>
                                    <x-icons.badge class="w-7 h-7" />
                                </span>
                                <span>
                                    <x-icons.badge class="w-7 h-7" />
                                </span>
                                <span>
                                    <x-icons.badge class="w-7 h-7" />
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Post Content Section -->
                    <div class="mt-6 border-t pt-6">
                        @auth
                            <div class="prose dark:prose-invert max-w-none">
                                {!! $post->content !!}
                            </div>
                        @else
                            <div class="prose dark:prose-invert max-w-none relative">
                                <!-- Limited content container with fixed height -->
                                <div class="content-limited">
                                    {!! $post->content !!}
                                </div>

                                <!-- Fade overlay - always visible -->
                                <div class="content-fade"></div>

                                <!-- Login restriction message -->
                                <div class="login-restriction-container">
                                    <div class="text-xl font-bold mb-4">Ups! Akses terbatas</div>
                                    <p class="mb-6">Daftar untuk melihat dan terlibat langsung dalam pertanyaan dan diskusi
                                    </p>
                                    <div class="flex flex-col justify-center items-center ">
                                        <a data-auth-action="register" class="flex cursor-pointer">
                                            <div
                                                class=" w-max px-4 py-2 mb-6 bg-branding-primary text-branding-light rounded-md text-sm font-medium shadow-md">
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

                    @auth
                        <!-- Action Bar -->
                        <div class="flex items-center justify-end mt-4">
                            <x-action-bar :model="$post" modelType="post" :showVoteScore="false" :showCommentCount="true"
                                :showShare="true" />
                        </div>

                        <!-- Answer form and list only for authenticated users -->

                        <!-- Include answer form partial -->
                        @include('posts.partials.answer-form', ['post' => $post])

                        <!-- Include answers list partial -->
                        @include('posts.partials.answers-list', ['post' => $post])
                    @endauth


                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .content-limited {
            max-height: 300px;
            overflow: hidden;
            position: relative;
            -webkit-mask-image: linear-gradient(to bottom, black 70%, transparent 100%);
            mask-image: linear-gradient(to bottom, black 70%, transparent 100%);
        }

        .content-fade {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 180px;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 70%);
            pointer-events: none;
            z-index: 10;
        }

        .dark .content-fade {
            background: linear-gradient(to bottom, rgba(31, 41, 55, 0) 0%, rgba(31, 41, 55, 1) 70%);
        }

        .login-restriction-container {
            position: relative;
            margin-top: 60px;
            padding: 20px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            z-index: 20;
        }

        .dark .login-restriction-container {
            background-color: rgba(31, 41, 55, 0.7);
        }

        .dark .content-fade {
            background: linear-gradient(to bottom, rgba(31, 41, 55, 0), rgba(31, 41, 55, 1));
        }
    </style>
@endpush

@push('scripts')
    <!-- No custom script needed here since we're using the global voting.js -->
@endpush
