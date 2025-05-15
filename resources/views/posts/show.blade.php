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

                    <div class="mb-6">
                        <h1 class="text-2xl font-bold mb-3">{{ $post->title }}</h1>

                        <div class="flex items-center">
                            <x-user-profile :user="$post->user" :date="$post->created_at" />

                            <div class="ml-auto flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">
                                    {{ $post->type === 'question' ? 'Pertanyaan' : 'Diskusi' }}
                                </span>
                                <span>{{ $post->view_count }} views</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-6">
                        <div class="prose dark:prose-invert max-w-none">
                            {!! $post->content !!}
                        </div>
                    </div>

                    <!-- Using the action-bar component -->
                    <div class="flex items-center justify-end mt-4">
                        <x-action-bar :model="$post" modelType="post" :showVoteScore="false" :showCommentCount="true"
                            :showShare="true" />
                    </div>

                    <!-- Include answer form partial -->
                    @include('posts.partials.answer-form', ['post' => $post])

                    <!-- Include answers list partial -->
                    @include('posts.partials.answers-list', ['post' => $post])

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('home') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:bg-gray-400 dark:focus:bg-gray-500 active:bg-gray-500 dark:active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Kembali ke Daftar
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- No custom script needed here since we're using the global voting.js -->
@endpush
