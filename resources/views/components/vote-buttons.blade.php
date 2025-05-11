@props([
    'model', // The model to vote on (post or answer)
    'modelType' => 'post', // Either 'post' or 'answer'
    'showScore' => true, // Whether to show the vote score
    'compact' => false, // Compact mode for smaller screens or tighter layouts
])

@php
    $routeName = $modelType === 'post' ? 'posts.vote' : 'answers.vote';
    $modelId = $model->id;
    $voteScore = $model->vote_score;
    $userVote = $model->user_vote;
    $dataAttr = "data-{$modelType}-id";

    // Define button styles based on compact mode
    $btnClass = $compact
        ? 'text-sm flex items-center hover:text-gray-700 dark:hover:text-gray-200'
        : 'inline-flex items-center text-xs px-2 py-1 rounded hover:bg-gray-200 dark:hover:bg-gray-600';

    // Define active states - now with branding color borders
    $upvoteActiveClass =
        $userVote === 1
            ? ($compact
                ? 'active-vote text-green-600 dark:text-green-400'
                : 'active-vote bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 border-2 border-branding-primary')
            : ($compact
                ? 'text-gray-500 dark:text-gray-400'
                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 border-2 border-branding-primary');

    $downvoteActiveClass =
        $userVote === -1
            ? ($compact
                ? 'active-vote text-red-600 dark:text-red-400'
                : 'active-vote bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 border-2 border-branding-dark')
            : ($compact
                ? 'text-gray-500 dark:text-gray-400'
                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 border-2 border-branding-dark');

    // Define guest button style - updated with branding borders
    $guestBtnClass = $compact
        ? 'text-sm flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
        : 'inline-flex items-center text-xs px-2 py-1 text-gray-600 dark:text-gray-400 border-2 border-branding-dark rounded hover:bg-gray-200 dark:hover:bg-gray-600';
@endphp

<div class="flex items-center {{ $compact ? 'space-x-4' : 'space-x-2' }} vote-container">
    @auth
        <form action="{{ route($routeName, $modelId) }}" method="POST" class="inline vote-form ">
            @csrf
            <input type="hidden" name="value" value="1">
            <button type="button" class="vote-btn upvote-btn  {{ $btnClass }} {{ $upvoteActiveClass }}" title="Upvote">
                <x-icons.upvote class="mx-1 h-4 w-4 mr-1" />
                Upvote
            </button>
        </form>

        @if ($showScore)
            <span
                class="vote-score {{ $compact ? 'text-sm text-gray-700 dark:text-gray-300' : 'text-xs font-medium px-2' }}"
                {{ $dataAttr }}="{{ $modelId }}">
                {{ $voteScore }}
            </span>
        @endif

        <form action="{{ route($routeName, $modelId) }}" method="POST" class="inline vote-form">
            @csrf
            <input type="hidden" name="value" value="-1">
            <button type="button" class="vote-btn downvote-btn {{ $btnClass }} {{ $downvoteActiveClass }}"
                title="Downvote">
                <x-icons.downvote class="mx-1 h-4 w-4 mr-1" />
                Downvote
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="vote-btn guest-vote {{ $guestBtnClass }} border-branding-primary"
            title="Login to vote">
            <x-icons.upvote class="mx-1 h-4 w-4 mr-1" />
            Upvote
        </a>

        @if ($showScore)
            <span
                class="vote-score {{ $compact ? 'text-sm text-gray-700 dark:text-gray-300' : 'text-xs font-medium px-2' }}">
                {{ $voteScore }}
            </span>
        @endif

        <a href="{{ route('login') }}" class="vote-btn guest-vote {{ $guestBtnClass }} border-branding-dark"
            title="Login to vote">
            <x-icons.downvote class="mx-1 h-4 w-4 mr-1" />
            Downvote
        </a>
    @endauth
</div>
