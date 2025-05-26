{{-- resources\views\components\action-bar.blade.php --}}
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
                top: 0;
                left: 0;
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

    // Generate share data
    $shareUrl =
        $modelType === 'post'
            ? route('posts.show', $model->slug ?? $model->id)
            : route('posts.show', $model->post->slug ?? $model->post->id) . '#answer-' . $model->id;

    $shareTitle = $modelType === 'post' ? $model->title : 'Answer to: ' . $model->post->title;

    $shareDescription =
        $modelType === 'post' ? Str::limit(strip_tags($model->body), 150) : Str::limit(strip_tags($model->body), 150);
@endphp

<div class="flex flex-wrap items-center justify-between {{ $customClasses }} w-full">
    <div class="action-bar-container flex flex-wrap items-center gap-2 sm:gap-3">
        {{-- Include vote buttons component --}}
        <x-vote-buttons :model="$model" :modelType="$modelType" :showScore="$showVoteScore" />

        {{-- Comment/Answer count - More compact on mobile --}}
        @if ($showCommentCount)
            <span class="flex items-center text-xs py-1 rounded-md text-gray-600 dark:text-gray-400">
                <x-icons.comment class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
                <span
                    class="hidden sm:inline">{{ $modelType === 'post' ? $model->answers->count() : $model->comments->count() }}</span>
                <span
                    class="sm:hidden">{{ $modelType === 'post' ? $model->answers->count() : $model->comments->count() }}</span>
            </span>
        @endif

        {{-- Share button - Simple inline component --}}
        @if ($showShare)
            <div class="share-button-wrapper">
                <x-simple-share-modal :shareUrl="$shareUrl" :shareTitle="$shareTitle" :shareDescription="$shareDescription" />
            </div>
        @endif
    </div>

    {{-- Three dots menu - Desktop dropdown, Mobile bottom sheet --}}
    @if ($showMenu && $showThreeDots)
        {{-- Desktop: Use dropdown component --}}
        <div class="hidden md:block">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button
                        class="flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none p-1"
                        type="button" aria-label="Menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    @if ($canEdit)
                        <x-dropdown-link :href="route($modelType . 's.edit', $model->id)">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Postingan
                                @if ($isAdmin && !$isOwner)
                                    (Admin)
                                @endif
                            </div>
                        </x-dropdown-link>
                    @endif

                    @if ($canDelete)
                        <form method="POST" action="{{ route($modelType . 's.destroy', $model->id) }}" x-data
                            class="w-full">
                            @csrf
                            @method('DELETE')
                            <x-dropdown-link href="#"
                                @click.prevent="if(confirm('Are you sure you want to delete this {{ $modelType }}?')) $el.closest('form').submit();">
                                <div class="flex items-center text-red-600 dark:text-red-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus Postingan
                                    @if ($isAdmin && !$isOwner)
                                        (Admin)
                                    @endif
                                </div>
                            </x-dropdown-link>
                        </form>
                    @endif

                    @if ($canFeature)
                        @if ($modelType === 'post')
                            <form method="POST" action="{{ route('posts.toggle-featured', $model->id) }}" x-data
                                class="w-full">
                                @csrf
                                <x-dropdown-link href="#" @click.prevent="$el.closest('form').submit();">
                                    <div class="flex items-center">
                                        <x-icons.lamp class="h-4 w-4 mr-2" />
                                        {{ $model->featured_type === 'none' ? 'Mark as Editor\'s Pick' : 'Remove from Editor\'s Pick' }}
                                    </div>
                                </x-dropdown-link>
                            </form>
                        @elseif ($modelType === 'answer')
                            <form method="POST" action="{{ route('answers.toggle-editors-pick', $model->id) }}" x-data
                                class="w-full">
                                @csrf
                                <x-dropdown-link href="#" @click.prevent="$el.closest('form').submit();">
                                    <div class="flex items-center">
                                        <x-icons.lamp class="h-4 w-4 mr-2" />
                                        {{ $model->is_editors_pick ? 'Remove from Editor\'s Pick' : 'Mark as Editor\'s Pick' }}
                                    </div>
                                </x-dropdown-link>
                            </form>
                        @endif
                    @endif
                </x-slot>
            </x-dropdown>
        </div>

        {{-- Mobile: Keep original bottom sheet implementation --}}
        <div x-data="{
            open: false,
            scrollY: 0,
            openMenu() {
                this.scrollY = window.scrollY;
                this.open = true;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${this.scrollY}px`;
                document.body.style.width = '100%';
                document.body.classList.add('overflow-hidden');
            },
            closeMenu() {
                this.open = false;
                document.body.classList.remove('overflow-hidden');
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                window.scrollTo(0, this.scrollY);
            }
        }" class="relative md:hidden">
            <!-- Three dots menu button - Mobile only -->
            <button @click.stop.prevent="openMenu()"
                class="flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none p-1"
                type="button" aria-label="Menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                </svg>
            </button>

            <!-- Backdrop for mobile -->
            <div x-cloak x-show="open" @click="closeMenu()"
                class="fixed inset-0 bg-black bg-opacity-50 z-40 transition-opacity duration-200 ease-in-out"
                x-transition:enter="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="opacity-100"
                x-transition:leave-end="opacity-0"></div>

            <!-- Mobile bottom sheet -->
            <div x-cloak x-show="open" @click.away="closeMenu()" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-4"
                class="fixed inset-x-0 bottom-0 w-full bg-white dark:bg-gray-800
                       rounded-t-xl shadow-lg z-50 focus:outline-none">
                <div class="py-1 divide-y divide-gray-200 dark:divide-gray-700">

                    @if ($canEdit)
                        <a href="{{ route($modelType . 's.edit', $model->id) }}"
                            class="flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span class="text-center w-full">
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
                                <span class="text-center w-full">
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
                                    <x-icons.lamp class="h-5 w-5 mr-2" />
                                    <span class="text-center w-full">
                                        {{ $model->featured_type === 'none' ? 'Mark as Editor\'s Pick' : 'Remove from Editor\'s Pick' }}
                                    </span>
                                </button>
                            </form>
                        @elseif ($modelType === 'answer')
                            <form method="POST" action="{{ route('answers.toggle-editors-pick', $model->id) }}"
                                x-data>
                                @csrf
                                <button type="button" @click.stop.prevent="$el.closest('form').submit();"
                                    class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-icons.lamp class="h-5 w-5 mr-2" />
                                    <span class="text-center w-full">
                                        {{ $model->is_editors_pick ? 'Remove from Editor\'s Pick' : 'Mark as Editor\'s Pick' }}
                                    </span>
                                </button>
                            </form>
                        @endif
                    @endif

                    <!-- Cancel button for mobile -->
                    <button type="button" @click.stop.prevent="closeMenu()"
                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="text-center w-full font-medium">Batal</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
    <style>
        /* Custom mobile optimizations for action bar */
        @media (max-width: 640px) {
            .action-bar-container {
                flex-wrap: nowrap;
                overflow-x: auto;
                scrollbar-width: none;
                /* Firefox */
                -ms-overflow-style: none;
                /* IE and Edge */
            }

            .action-bar-container::-webkit-scrollbar {
                display: none;
                /* Chrome, Safari, Opera */
            }

            .share-button-wrapper {
                flex-shrink: 0;
            }
        }
    </style>
@endpush
