{{-- resources\views\components\vote-buttons.blade.php --}}
@props([
    'model', // The model to vote on (post or answer)
    'modelType' => 'post', // Either 'post' or 'answer'
    'showScore' => false, // Whether to show the vote score
])

@php
    $routeName = $modelType === 'post' ? 'posts.vote' : 'answers.vote';
    $modelId = $model->id;
    $voteScore = $model->vote_score;
    $userVote = $model->user_vote;
    $dataAttr = "data-{$modelType}-id";

    // Define responsive button styles - smaller on mobile
    $btnClass = 'inline-flex items-center text-xs px-2 py-1 rounded transition-colors duration-200';

    // Define active states with border styling but no background, text black
    $upvoteActiveClass =
        $userVote === 1
            ? 'active-vote text-black dark:text-white border-2 border-branding-primary'
            : 'text-black dark:text-white border-2 border-branding-primary hover:bg-branding-primary hover:bg-opacity-10';

    $downvoteActiveClass =
        $userVote === -1
            ? 'active-vote text-black dark:text-white border-2 border-branding-dark'
            : 'text-black dark:text-white border-2 border-branding-dark hover:bg-branding-dark hover:bg-opacity-10';

    // Define guest button style with hover states
    $guestBtnClass =
        'inline-flex items-center text-xs px-2 py-1 text-black dark:text-white border-2 rounded transition-colors duration-200';
@endphp

<div class="flex items-center space-x-1 sm:space-x-2 vote-container" {{ $dataAttr }}="{{ $modelId }}">
    @auth
        <form action="{{ route($routeName, $modelId) }}" method="POST" class="inline vote-form">
            @csrf
            <input type="hidden" name="value" value="1">
            <button type="button" class="vote-btn upvote-btn {{ $btnClass }} {{ $upvoteActiveClass }}" title="Upvote">
                <span class="icon-container">
                    <x-icons.upvote class="h-3 w-3 sm:h-4 sm:w-4 mr-1 {{ $userVote === 1 ? 'hidden' : '' }}" />
                    <x-icons.upvote-clicked class="h-3 w-3 sm:h-4 sm:w-4 mr-1 {{ $userVote === 1 ? '' : 'hidden' }}" />
                </span>
                Upvote
            </button>
        </form>

        @if ($showScore)
            <span class="vote-score text-xs font-medium px-1 sm:px-2" {{ $dataAttr }}="{{ $modelId }}">
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
                    <x-icons.downvote class="h-3 w-3 sm:h-4 sm:w-4 mr-1 {{ $userVote === -1 ? 'hidden' : '' }}" />
                    <x-icons.downvote-clicked class="h-3 w-3 sm:h-4 sm:w-4 mr-1 {{ $userVote === -1 ? '' : 'hidden' }}" />
                </span>
                Downvote
            </button>
        </form>
    @else
        <button type="button" data-auth-action="login"
            class="vote-btn guest-vote {{ $guestBtnClass }} border-branding-primary hover:bg-branding-primary hover:bg-opacity-10"
            title="Login to vote">
            <x-icons.upvote class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
            Upvote
        </button>

        @if ($showScore)
            <span class="vote-score text-xs font-medium px-1 sm:px-2">
                {{ $voteScore }}
            </span>
        @else
            <span class="hidden vote-score" {{ $dataAttr }}="{{ $modelId }}">{{ $voteScore }}</span>
        @endif

        <button type="button" data-auth-action="login"
            class="vote-btn guest-vote {{ $guestBtnClass }} border-branding-dark hover:bg-branding-dark hover:bg-opacity-10"
            title="Login to vote">
            <x-icons.downvote class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
            Downvote
        </button>
    @endauth
</div>
