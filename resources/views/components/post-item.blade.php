{{-- resources/views/components/post-item.blade.php --}}
@props([
    'post',
    'showMeta' => true, // Show author, time, views
    'showExcerpt' => true, // Show content excerpt
    'showFeaturedIcon' => true, // Show featured lightbulb icon
    'showActionBar' => true, // Show action bar (votes, comments, share)
    'showVoteScore' => false, // Show vote score in action bar
    'showCommentCount' => true, // Show comment count in action bar
    'showShare' => true, // Show share button in action bar
    'showThreeDots' => true, // Show three dots menu in action bar
    'customClasses' => '', // Custom classes for action bar
    'containerClasses' => 'bg-white dark:bg-gray-800 border-b border-essentials-inactive', // Container styling
    'isHighlighted' => false, // For editor's picks highlighting
    'excerptLength' => 100, // Length of excerpt
])

@php
    // Determine container classes
    $containerClass = $isHighlighted
        ? 'bg-branding-primary/20 dark:bg-branding-primary/10 border-b border-essentials-inactive'
        : $containerClasses;
@endphp

<div class="{{ $containerClass }}">
    <div>
        <a href="{{ route('posts.show', $post->id) }}">
            <div class="flex justify-between items-start">
                <div class="flex-col">
                    @if ($showMeta)
                        <div class="flex items-center py-2 space-x-4 text-xs text-gray-500 dark:text-gray-400">
                            <span>By: {{ $post->user->name ?? 'Unknown' }}</span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                            <span>{{ $post->view_count }} views</span>
                        </div>
                    @endif

                    <h3
                        class="font-bold text-lg text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                        {{ $post->title }}
                    </h3>

                    @if ($showExcerpt)
                        <p class="text-gray-600 dark:text-gray-400 mt-2">
                            @excerpt($post->content, $excerptLength, '...')
                        </p>
                    @endif
                </div>

                @if ($showFeaturedIcon && $post->is_featured && $post->featured_type !== 'none')
                    <div class="flex items-center">
                        <x-icons.lightbulb class="h-10 w-10 text-orange-500" />
                    </div>
                @endif
            </div>
        </a>

        @if ($showActionBar)
            <div class="flex items-center mt-3 relative">
                <x-action-bar :model="$post" modelType="post" :showVoteScore="$showVoteScore" :showCommentCount="$showCommentCount" :showShare="$showShare"
                    :showThreeDots="$showThreeDots" :customClasses="$customClasses" />
            </div>
        @endif
    </div>
</div>
