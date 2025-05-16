@props([
    'model', // The model to vote on (post or answer)
    'modelType' => 'post', // Either 'post' or 'answer'
    'showVoteScore' => false, // Whether to show the vote score in the vote buttons
    'showCommentCount' => true, // Whether to show comment/answer count
    'showShare' => true, // Whether to show share button
    'showThreeDots' => true, // Whether to show the three dots menu
    'customClasses' => '', // Additional custom classes for the container
])

@once
    @push('styles')
        <style>
            /* Hide elements with x-cloak until Alpine.js has initialized */
            [x-cloak] {
                display: none !important;
            }

            /* Only needed for preventing body scroll on mobile when dropdown is open */
            body.overflow-hidden {
                overflow: hidden;
                position: fixed;
                width: 100%;
            }
        </style>
    @endpush
@endonce

@php
    $isOwner = Auth::check() && $model->user_id === Auth::id();
    $isAdmin = Auth::check() && Auth::user()->hasRole('admin');
    $isEditor = Auth::check() && Auth::user()->hasRole('editor');

    // Determine if menu should be shown
    $showMenu = $isOwner || $isAdmin || $isEditor;

    // Determine edit/delete permissions
    $canEdit = ($isOwner || $isAdmin) && $modelType === 'post'; // Only allow editing for posts, not answers
    $canDelete = $isOwner || $isAdmin;

    // Feature/editors pick permissions
    $canFeature = $isAdmin || $isEditor;
@endphp

<div class="flex flex-wrap items-center justify-between {{ $customClasses }} w-full">
    <div class="action-bar-container flex flex-wrap items-center gap-3">
        {{-- Include vote buttons component --}}
        <x-vote-buttons :model="$model" :modelType="$modelType" :showScore="$showVoteScore" />

        {{-- Comment/Answer count --}}
        @if ($showCommentCount)
            <span class="flex items-center text-xs py-1 rounded-md">
                <x-icons.comment class="h-4 w-4 mr-1" />
                {{ $modelType === 'post' ? $model->answers->count() : $model->comments->count() }}
            </span>
        @endif

        {{-- Share button --}}
        @if ($showShare)
            <button type="button" class="flex items-center text-xs py-1 rounded-md">
                <x-icons.share class="h-4 w-4 mr-1" />
            </button>
        @endif
    </div>

    {{-- Three dots menu - Show to post owner, admins, or editors with appropriate permissions --}}
    @if ($showMenu && $showThreeDots)
        <div x-data="{ open: false }" class="relative">
            <!-- Three dots menu button -->
            <button
                @click.stop.prevent="open = !open; if (open && window.innerWidth < 768) document.body.classList.add('overflow-hidden'); else document.body.classList.remove('overflow-hidden')"
                class="flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none"
                type="button" aria-label="Menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                </svg>
            </button>

            <!-- Backdrop for mobile only using Tailwind -->
            <div x-cloak x-show="open"
                @click.stop.prevent="open = false; document.body.classList.remove('overflow-hidden')"
                class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden transition-opacity duration-200 ease-in-out"
                x-transition:enter="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="opacity-100"
                x-transition:leave-end="opacity-0"></div>

            <!-- Dropdown menu with Tailwind transitions -->
            <div x-cloak x-show="open" @click.away="open = false; document.body.classList.remove('overflow-hidden')"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-4 md:translate-y-0 md:scale-95"
                x-transition:enter-end="opacity-100 transform translate-y-0 md:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0 md:scale-100"
                x-transition:leave-end="opacity-0 transform translate-y-4 md:translate-y-0 md:scale-95"
                class="fixed inset-x-0 bottom-0 md:absolute md:right-0 md:bottom-auto md:mt-2
                       w-full md:w-48 bg-white dark:bg-gray-800
                       rounded-t-xl md:rounded-md shadow-lg md:ring-1 md:ring-black md:ring-opacity-5
                       z-50 focus:outline-none">
                <div class="py-1 divide-y divide-gray-200 dark:divide-gray-700">
                    @if ($canEdit)
                        <a href="{{ route($modelType . 's.edit', $model->id) }}"
                            class="flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span class="text-center w-full md:text-left md:w-auto">
                                Edit Postingan
                                @if ($isAdmin && !$isOwner)
                                    (Admin)
                                @endif
                            </span>
                        </a>
                    @endif

                    @if ($canDelete)
                        <form method="POST" action="{{ route($modelType . 's.destroy', $model->id) }}" x-data>
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                @click.stop.prevent="if(confirm('Are you sure you want to delete this {{ $modelType }}?')) $el.closest('form').submit();"
                                class="flex items-center w-full px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="text-center w-full md:text-left md:w-auto">
                                    Hapus Postingan
                                    @if ($isAdmin && !$isOwner)
                                        (Admin)
                                    @endif
                                </span>
                            </button>
                        </form>
                    @endif

                    @if ($canFeature)
                        @if ($modelType === 'post')
                            <form method="POST" action="{{ route('posts.toggle-featured', $model->id) }}" x-data>
                                @csrf
                                <button type="button" @click.stop.prevent="$el.closest('form').submit();"
                                    class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-icons.lightbulb class="h-5 w-5 mr-2" />
                                    <span class="text-center w-full md:text-left md:w-auto">
                                        {{ $model->featured_type === 'none' ? 'Mark as Editor\'s Pick' : 'Remove from Editor\'s Pick' }}
                                    </span>
                                </button>
                            </form>
                        @elseif ($modelType === 'answer')
                            <form method="POST" action="{{ route('answers.toggle-editors-pick', $model->id) }}" x-data>
                                @csrf
                                <button type="button" @click.stop.prevent="$el.closest('form').submit();"
                                    class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-icons.lightbulb class="h-5 w-5 mr-2" />
                                    <span class="text-center w-full md:text-left md:w-auto">
                                        {{ $model->is_editors_pick ? 'Remove from Editor\'s Pick' : 'Mark as Editor\'s Pick' }}
                                    </span>
                                </button>
                            </form>
                        @endif
                    @endif

                    <!-- Cancel button for mobile -->
                    <button type="button"
                        @click.stop.prevent="open = false; document.body.classList.remove('overflow-hidden')"
                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 md:hidden">
                        <span class="text-center w-full font-medium">Batal</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
