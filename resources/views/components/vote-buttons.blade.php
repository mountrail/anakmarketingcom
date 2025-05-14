@props([
    'model', // The model to vote on (post or answer)
    'modelType' => 'post', // Either 'post' or 'answer'
    'showScore' => false, // Whether to show the vote score
    'compact' => false, // Compact mode for smaller screens or tighter layouts
])

@php
    $routeName = $modelType === 'post' ? 'posts.vote' : 'answers.vote';
    $modelId = $model->id;
    $voteScore = $model->vote_score;
    $userVote = $model->user_vote;
    $dataAttr = "data-{$modelType}-id";

    // Define button styles based on compact mode
    $btnClass = $compact ? 'text-sm flex items-center' : 'inline-flex items-center text-xs px-2 py-1 rounded';

    // Define active states with border styling but no background, text black
    $upvoteActiveClass =
        $userVote === 1
            ? ($compact
                ? 'active-vote text-black dark:text-white'
                : 'active-vote text-black dark:text-white border-2 border-branding-primary')
            : ($compact
                ? 'text-black dark:text-white'
                : 'text-black dark:text-white border-2 border-branding-primary');

    $downvoteActiveClass =
        $userVote === -1
            ? ($compact
                ? 'active-vote text-black dark:text-white'
                : 'active-vote text-black dark:text-white border-2 border-branding-dark')
            : ($compact
                ? 'text-black dark:text-white'
                : 'text-black dark:text-white border-2 border-branding-dark');

    // Define guest button style
    $guestBtnClass = $compact
        ? 'text-sm flex items-center text-black dark:text-white'
        : 'inline-flex items-center text-xs px-2 py-1 text-black dark:text-white border-2 border-branding-dark rounded';
@endphp

<div class="flex items-center {{ $compact ? 'space-x-4' : 'space-x-2' }} vote-container"
    {{ $dataAttr }}="{{ $modelId }}">
    @auth
        <form action="{{ route($routeName, $modelId) }}" method="POST" class="inline vote-form">
            @csrf
            <input type="hidden" name="value" value="1">
            <button type="button" class="vote-btn upvote-btn {{ $btnClass }} {{ $upvoteActiveClass }}" title="Upvote">
                <span class="icon-container">
                    <x-icons.upvote class="mx-1 h-4 w-4 mr-1 {{ $userVote === 1 ? 'hidden' : '' }}" />
                    <x-icons.upvote-clicked class="mx-1 h-4 w-4 mr-1 {{ $userVote === 1 ? '' : 'hidden' }}" />
                </span>
                Upvote
            </button>
        </form>

        @if ($showScore)
            <span
                class="vote-score {{ $compact ? 'text-sm text-gray-700 dark:text-gray-300' : 'text-xs font-medium px-2' }}"
                {{ $dataAttr }}="{{ $modelId }}">
                {{ $voteScore }}
            </span>
        @else
            <span class="hidden vote-score" {{ $dataAttr }}="{{ $modelId }}">{{ $voteScore }}</span>
        @endif

        <form action="{{ route($routeName, $modelId) }}" method="POST" class="inline vote-form">
            @csrf
            <input type="hidden" name="value" value="-1">
            <button type="button" class="vote-btn downvote-btn {{ $btnClass }} {{ $downvoteActiveClass }}"
                title="Downvote">
                <span class="icon-container">
                    <x-icons.downvote class="mx-1 h-4 w-4 mr-1 {{ $userVote === -1 ? 'hidden' : '' }}" />
                    <x-icons.downvote-clicked class="mx-1 h-4 w-4 mr-1 {{ $userVote === -1 ? '' : 'hidden' }}" />
                </span>
                Downvote
            </button>
        </form>
    @else
        <a data-auth-action="login" class="vote-btn guest-vote {{ $guestBtnClass }} border-branding-primary"
            title="Login to vote">
            <x-icons.upvote class="mx-1 h-4 w-4 mr-1" />
            Upvote
        </a>

        @if ($showScore)
            <span
                class="vote-score {{ $compact ? 'text-sm text-gray-700 dark:text-gray-300' : 'text-xs font-medium px-2' }}">
                {{ $voteScore }}
            </span>
        @else
            <span class="hidden vote-score" {{ $dataAttr }}="{{ $modelId }}">{{ $voteScore }}</span>
        @endif

        <a data-auth-action="login" class="vote-btn guest-vote {{ $guestBtnClass }} border-branding-dark"
            title="Login to vote">
            <x-icons.downvote class="mx-1 h-4 w-4 mr-1" />
            Downvote
        </a>
    @endauth
</div>
