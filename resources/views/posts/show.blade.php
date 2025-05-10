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

                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold">{{ $post->title }}</h1>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <span>{{ $post->type === 'question' ? 'Pertanyaan' : 'Diskusi' }}</span>
                                <span>Oleh: {{ $post->user->name }}</span>
                                <span>{{ $post->created_at->format('d M Y H:i') }}</span>
                                <span>{{ $post->view_count }} views</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-6">
                        <div class="prose dark:prose-invert max-w-none">
                            {!! $post->content !!}
                        </div>
                    </div>

                    <!-- Updated to match post-list.blade.php voting UI -->
                    <div class="flex items-center justify-end mt-4">
                        <div class="flex items-center space-x-2">
                            <!-- Vote buttons -->
                            <div class="flex items-center space-x-2 vote-container">
                                @auth
                                    <form action="{{ route('posts.vote', $post->id) }}" method="POST"
                                        class="inline vote-form">
                                        @csrf
                                        <input type="hidden" name="value" value="1">
                                        <button type="button"
                                            class="vote-btn upvote-btn inline-flex items-center text-xs px-2 py-1 {{ $post->user_vote === 1 ? 'active-vote bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }} rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                            title="Upvote">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                            Upvote
                                        </button>
                                    </form>

                                    <span class="vote-score text-xs font-medium px-2"
                                        data-post-id="{{ $post->id }}">
                                        {{ $post->vote_score }}
                                    </span>

                                    <form action="{{ route('posts.vote', $post->id) }}" method="POST"
                                        class="inline vote-form">
                                        @csrf
                                        <input type="hidden" name="value" value="-1">
                                        <button type="button"
                                            class="vote-btn downvote-btn inline-flex items-center text-xs px-2 py-1 {{ $post->user_vote === -1 ? 'active-vote bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }} rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                            title="Downvote">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            Downvote
                                        </button>
                                    </form>
                                @else
                                    <button type="button"
                                        class="vote-btn guest-vote inline-flex items-center text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                        title="Login to vote">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        Upvote
                                    </button>

                                    <span class="vote-score text-xs font-medium px-2">
                                        {{ $post->vote_score }}
                                    </span>

                                    <button type="button"
                                        class="vote-btn guest-vote inline-flex items-center text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                        title="Login to vote">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        Downvote
                                    </button>
                                @endauth
                            </div>
                            <span
                                class="inline-flex items-center text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                {{ $post->answers->count() }}
                            </span>
                            <button
                                class="inline-flex items-center text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                Share
                            </button>
                        </div>
                    </div>

                    <!-- Include answer form component -->
                    <x-answer-form :post="$post" />
                    <!-- Include answers list component -->
                    <x-answers-list :post="$post" />


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
