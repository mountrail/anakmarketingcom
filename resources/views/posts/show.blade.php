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

                        @auth
                            <div class="flex items-center space-x-2">
                                <form action="{{ route('posts.vote', $post->id) }}" method="POST" class="inline vote-form">
                                    @csrf
                                    <input type="hidden" name="value" value="1">
                                    <button type="submit"
                                        class="vote-btn upvote-btn inline-flex items-center p-2 {{ $post->user_vote === 1 ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }} rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                        title="Upvote">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                </form>

                                <span class="vote-score text-lg font-medium px-2" data-post-id="{{ $post->id }}">
                                    {{ $post->vote_score }}
                                </span>

                                <form action="{{ route('posts.vote', $post->id) }}" method="POST" class="inline vote-form">
                                    @csrf
                                    <input type="hidden" name="value" value="-1">
                                    <button type="submit"
                                        class="vote-btn downvote-btn inline-flex items-center p-2 {{ $post->user_vote === -1 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }} rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                        title="Downvote">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center space-x-2">
                                <span class="vote-score text-lg font-medium px-2">
                                    {{ $post->vote_score }}
                                </span>
                                <a href="{{ route('login') }}"
                                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                    Log in to vote
                                </a>
                            </div>
                        @endauth
                    </div>

                    <div class="mt-6 border-t pt-6">
                        <div class="prose dark:prose-invert max-w-none">
                            {!! $post->content !!}
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle vote forms submission via AJAX
            const voteForms = document.querySelectorAll('.vote-form');

            voteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const url = form.getAttribute('action');
                    const voteValue = formData.get('value');
                    const isPostVote = url.includes('/posts/');
                    const targetId = url.match(/\/(posts|answers)\/(\d+)\/vote/)[2];

                    fetch(url, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': formData.get('_token')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Update the score display
                            const scoreElement = document.querySelector(
                                `.vote-score[data-${isPostVote ? 'post' : 'answer'}-id="${targetId}"]`
                            );
                            if (scoreElement) {
                                scoreElement.textContent = data.score;
                            }

                            // Update button styles
                            const upvoteBtn = form.closest('.flex').querySelector(
                                '.upvote-btn');
                            const downvoteBtn = form.closest('.flex').querySelector(
                                '.downvote-btn');

                            // Reset button styles
                            upvoteBtn.classList.remove('bg-green-100', 'dark:bg-green-900',
                                'text-green-600', 'dark:text-green-400');
                            upvoteBtn.classList.add('bg-gray-100', 'dark:bg-gray-700',
                                'text-gray-600', 'dark:text-gray-400');

                            downvoteBtn.classList.remove('bg-red-100', 'dark:bg-red-900',
                                'text-red-600', 'dark:text-red-400');
                            downvoteBtn.classList.add('bg-gray-100', 'dark:bg-gray-700',
                                'text-gray-600', 'dark:text-gray-400');

                            // Set active style if needed
                            if (data.userVote === 1) {
                                upvoteBtn.classList.remove('bg-gray-100', 'dark:bg-gray-700',
                                    'text-gray-600', 'dark:text-gray-400');
                                upvoteBtn.classList.add('bg-green-100', 'dark:bg-green-900',
                                    'text-green-600', 'dark:text-green-400');
                            } else if (data.userVote === -1) {
                                downvoteBtn.classList.remove('bg-gray-100', 'dark:bg-gray-700',
                                    'text-gray-600', 'dark:text-gray-400');
                                downvoteBtn.classList.add('bg-red-100', 'dark:bg-red-900',
                                    'text-red-600', 'dark:text-red-400');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                });
            });
        });
    </script>
@endpush
