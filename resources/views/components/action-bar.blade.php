@props([
    'model', // The model to vote on (post or answer)
    'modelType' => 'post', // Either 'post' or 'answer'
    'showVoteScore' => false, // Whether to show the vote score in the vote buttons
    'compact' => false, // Compact mode for smaller screens or tighter layouts
    'showCommentCount' => true, // Whether to show comment/answer count
    'showShare' => true, // Whether to show share button
    'customClasses' => '', // Additional custom classes for the container
])

<div class="flex items-center justify-between {{ $customClasses }} w-full">
    <div class="flex items-center space-x-2">
        {{-- Include vote buttons component --}}
        <x-vote-buttons :model="$model" :modelType="$modelType" :showScore="$showVoteScore" :compact="$compact" />

        {{-- Comment/Answer count --}}
        @if ($showCommentCount)
            <span class="flex items-center text-xs px-2 py-1 rounded-md">
                <x-icons.comment class="h-4 w-4 mr-1" />
                {{ $modelType === 'post' ? $model->answers->count() : $model->comments->count() }}
            </span>
        @endif

        {{-- Share button --}}
        @if ($showShare)
            <button class="flex items-center text-xs px-2 py-1 rounded-md">
                <x-icons.share class="h-4 w-4 mr-1" />
                Share
            </button>
        @endif
    </div>

    {{-- Three dots menu - Show to post owner, admins, or editors with appropriate permissions --}}
    @php
        $isOwner = Auth::check() && $model->user_id === Auth::id();
        $isAdmin = Auth::check() && Auth::user()->hasRole('admin');
        $isEditor = Auth::check() && Auth::user()->hasRole('editor');

        // Determine if menu should be shown
        $showMenu = $isOwner || $isAdmin || $isEditor;

        // Determine edit/delete permissions
        $canEdit = $isOwner || $isAdmin;
        $canDelete = $isOwner || $isAdmin;

        // Feature/editors pick permissions
        $canFeature = $isAdmin || $isEditor;
    @endphp

    @if ($showMenu)
        <div>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button
                        class="flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    @if ($canEdit)
                        <x-dropdown-link href="{{ route($modelType . 's.edit', $model->id) }}">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                                @if ($isAdmin && !$isOwner)
                                    (Admin)
                                @endif
                            </div>
                        </x-dropdown-link>
                    @endif

                    @if ($canDelete)
                        <form method="POST" action="{{ route($modelType . 's.destroy', $model->id) }}">
                            @csrf
                            @method('DELETE')
                            <x-dropdown-link href="#"
                                onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this {{ $modelType }}?')) this.closest('form').submit();">
                                <div class="flex items-center text-red-600 dark:text-red-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                    @if ($isAdmin && !$isOwner)
                                        (Admin)
                                    @endif
                                </div>
                            </x-dropdown-link>
                        </form>
                    @endif

                    @if ($canFeature)
                        @if ($modelType === 'post')
                            <form method="POST" action="{{ route('posts.toggle-featured', $model->id) }}">
                                @csrf
                                <x-dropdown-link href="#"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    <div class="flex items-center">
                                        <x-icons.lightbulb class="h-5 w-5 mr-2" />
                                        {{ $model->featured_type === 'none' ? 'Mark as Editor\'s Pick' : 'Remove from Editor\'s Pick' }}
                                    </div>
                                </x-dropdown-link>
                            </form>
                        @elseif ($modelType === 'answer')
                            <form method="POST" action="{{ route('answers.toggle-editors-pick', $model->id) }}">
                                @csrf
                                <x-dropdown-link href="#"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    <div class="flex items-center">
                                        <x-icons.lightbulb class="h-5 w-5 mr-2" />
                                        {{ $model->is_editors_pick ? 'Remove from Editor\'s Pick' : 'Mark as Editor\'s Pick' }}
                                    </div>
                                </x-dropdown-link>
                            </form>
                        @endif
                    @endif
                </x-slot>
            </x-dropdown>
        </div>
    @endif
</div>
