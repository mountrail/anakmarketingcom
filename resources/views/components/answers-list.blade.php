@props(['post'])

<div class="mt-8 border-t pt-6">
    <h3 class="text-xl font-semibold mb-4">
        {{ $post->answers->count() }} {{ Str::plural('Answer', $post->answers->count()) }}
    </h3>

    @if ($post->answers->count() > 0)
        <div class="space-y-6">
            @foreach ($post->answers->sortByDesc('is_editors_pick')->sortByDesc('created_at') as $answer)
                <div class="border-b pb-6 last:border-b-0" id="answer-{{ $answer->id }}">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-3">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100">{{ $answer->user->name }}</span>
                                •
                                <span>{{ $answer->created_at->diffForHumans() }}</span>
                            </div>

                            @if ($answer->is_editors_pick)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                    Editor's Pick
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 text-gray-900 dark:text-gray-100">
                        {!! Str::limit(strip_tags($answer->content, '<p>'), 100, '...') !!}
                    </div>

                    <div class="mt-4 flex space-x-4">
                        <!-- Replace the existing vote buttons in answers-list.blade.php -->
                        <div class="mt-4 flex space-x-4 vote-container">
                            @auth
                                <form action="{{ route('answers.vote', $answer->id) }}" method="POST"
                                    class="inline vote-form">
                                    @csrf
                                    <input type="hidden" name="value" value="1">
                                    <button type="button"
                                        class="vote-btn upvote-btn text-sm flex items-center hover:text-gray-700 dark:hover:text-gray-200 {{ $answer->user_vote === 1 ? 'active-vote text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        Upvote
                                    </button>
                                </form>

                                <span class="vote-score text-sm text-gray-700 dark:text-gray-300"
                                    data-answer-id="{{ $answer->id }}">
                                    {{ $answer->vote_score }}
                                </span>

                                <form action="{{ route('answers.vote', $answer->id) }}" method="POST"
                                    class="inline vote-form">
                                    @csrf
                                    <input type="hidden" name="value" value="-1">
                                    <button type="button"
                                        class="vote-btn downvote-btn text-sm flex items-center hover:text-gray-700 dark:hover:text-gray-200 {{ $answer->user_vote === -1 ? 'active-vote text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        Downvote
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}"
                                    class="vote-btn guest-vote text-sm flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7" />
                                    </svg>
                                    Upvote
                                </a>

                                <span class="vote-score text-sm text-gray-700 dark:text-gray-300">
                                    {{ $answer->vote_score }}
                                </span>

                                <a href="{{ route('login') }}"
                                    class="vote-btn guest-vote text-sm flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                    Downvote
                                </a>
                            @endauth
                            <!-- Editor's Pick Toggle (visible only to admins with the right permissions) -->
                            @can('manage-editor-picks')
                                <form method="POST" action="{{ route('answers.toggle-editors-pick', $answer->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="text-sm text-gray-500 dark:text-gray-400 flex items-center hover:text-orange-500 dark:hover:text-orange-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        {{ $answer->is_editors_pick ? 'Remove Editor\'s Pick' : 'Mark as Editor\'s Pick' }}
                                    </button>
                                </form>
                            @endcan
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-gray-500 dark:text-gray-400">
            No answers yet. Be the first to share your knowledge!
        </div>
    @endif
</div>
