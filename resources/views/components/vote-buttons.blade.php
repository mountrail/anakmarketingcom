{{-- resources\views\components\vote-buttons.blade.php --}}
@props([
    'model', // The model to vote on (post or answer)
    'modelType' => 'post', // Either 'post' or 'answer'
    'showScore' => false, // Whether to show the vote score
    'showUpvoteCount' => true, // Whether to show upvote count
    'showDownvoteCount' => false, // Whether to show downvote count
])

@php
    $routeName = $modelType === 'post' ? 'posts.vote' : 'answers.vote';
    // Always use ID for voting - more reliable than slug
    $routeParam = $model->id;
    $modelId = $model->id;
    $voteScore = $model->vote_score;
    $userVote = $model->user_vote;
    $dataAttr = "data-{$modelType}-id";

    // Calculate upvote and downvote counts (using count instead of sum for display)
    $upvoteCount = $model->votes()->where('value', 1)->count();
    $downvoteCount = $model
        ->votes()
        ->where('value', -1)
        ->count();

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
        {{-- Upvote Button - Always visible --}}
        <form action="{{ route($routeName, $routeParam) }}" method="POST" class="inline vote-form">
            @csrf
            <input type="hidden" name="value" value="1">
            <button type="button" class="vote-btn upvote-btn {{ $btnClass }} {{ $upvoteActiveClass }}" title="Upvote">
                <span class="icon-container">
                    <x-icons.upvote class="h-3 w-3 sm:h-4 sm:w-4 mr-1 {{ $userVote === 1 ? 'hidden' : '' }}" />
                    <x-icons.upvote-clicked class="h-3 w-3 sm:h-4 sm:w-4 mr-1 {{ $userVote === 1 ? '' : 'hidden' }}" />
                </span>
                <span class="flex items-center">
                    Upvote
                    @if ($showUpvoteCount)
                        <span class="ml-1 text-gray-500 dark:text-gray-400">|</span>
                        <span class="ml-1 upvote-count" {{ $dataAttr }}="{{ $modelId }}">{{ $upvoteCount }}</span>
                    @endif
                </span>
            </button>
        </form>

        {{-- Vote Score --}}
        @if ($showScore)
            <span class="vote-score text-xs font-medium px-1 sm:px-2" {{ $dataAttr }}="{{ $modelId }}">
                {{ $voteScore }}
            </span>
        @else
            <span class="hidden vote-score" {{ $dataAttr }}="{{ $modelId }}">{{ $voteScore }}</span>
        @endif

        {{-- Downvote Button - Always visible --}}
        <form action="{{ route($routeName, $routeParam) }}" method="POST" class="inline vote-form">
            @csrf
            <input type="hidden" name="value" value="-1">
            <button type="button" class="vote-btn downvote-btn {{ $btnClass }} {{ $downvoteActiveClass }}"
                title="Downvote">
                <span class="icon-container">
                    <x-icons.downvote class="h-3 w-3 sm:h-4 sm:w-4 mr-1 {{ $userVote === -1 ? 'hidden' : '' }}" />
                    <x-icons.downvote-clicked class="h-3 w-3 sm:h-4 sm:w-4 mr-1 {{ $userVote === -1 ? '' : 'hidden' }}" />
                </span>
                <span class="flex items-center">
                    Downvote
                    @if ($showDownvoteCount)
                        <span class="ml-1 text-gray-500 dark:text-gray-400">|</span>
                        <span class="ml-1 downvote-count"
                            {{ $dataAttr }}="{{ $modelId }}">{{ $downvoteCount }}</span>
                    @endif
                </span>
            </button>
        </form>

        {{-- Hidden count elements for JavaScript (when counts are not shown) --}}
        @if (!$showUpvoteCount)
            <span class="hidden upvote-count" {{ $dataAttr }}="{{ $modelId }}">{{ $upvoteCount }}</span>
        @endif
        @if (!$showDownvoteCount)
            <span class="hidden downvote-count" {{ $dataAttr }}="{{ $modelId }}">{{ $downvoteCount }}</span>
        @endif
    @else
        {{-- Guest Upvote Button - Always visible --}}
        <button type="button" data-auth-action="login"
            class="vote-btn guest-vote {{ $guestBtnClass }} border-branding-primary hover:bg-branding-primary hover:bg-opacity-10"
            title="Login to vote">
            <x-icons.upvote class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
            <span class="flex items-center">
                Upvote
                @if ($showUpvoteCount)
                    <span class="ml-1 text-gray-500 dark:text-gray-400">|</span>
                    <span class="ml-1 upvote-count" {{ $dataAttr }}="{{ $modelId }}">{{ $upvoteCount }}</span>
                @endif
            </span>
        </button>

        {{-- Guest Vote Score --}}
        @if ($showScore)
            <span class="vote-score text-xs font-medium px-1 sm:px-2">
                {{ $voteScore }}
            </span>
        @else
            <span class="hidden vote-score" {{ $dataAttr }}="{{ $modelId }}">{{ $voteScore }}</span>
        @endif

        {{-- Guest Downvote Button - Always visible --}}
        <button type="button" data-auth-action="login"
            class="vote-btn guest-vote {{ $guestBtnClass }} border-branding-dark hover:bg-branding-dark hover:bg-opacity-10"
            title="Login to vote">
            <x-icons.downvote class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
            <span class="flex items-center">
                Downvote
                @if ($showDownvoteCount)
                    <span class="ml-1 text-gray-500 dark:text-gray-400">|</span>
                    <span class="ml-1 downvote-count"
                        {{ $dataAttr }}="{{ $modelId }}">{{ $downvoteCount }}</span>
                @endif
            </span>
        </button>

        {{-- Hidden count elements for JavaScript (when counts are not shown) --}}
        @if (!$showUpvoteCount)
            <span class="hidden upvote-count" {{ $dataAttr }}="{{ $modelId }}">{{ $upvoteCount }}</span>
        @endif
        @if (!$showDownvoteCount)
            <span class="hidden downvote-count" {{ $dataAttr }}="{{ $modelId }}">{{ $downvoteCount }}</span>
        @endif
    @endauth
</div>
