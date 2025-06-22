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
    'containerClasses' => ' p-4 px-6 border-b border-essentials-inactive', // Container styling
    'isHighlighted' => false, // For editor's picks highlighting
    'excerptLength' => 100, // Length of excerpt (for character-based excerpts)
    'excerptLines' => 3, // Number of lines for line-based excerpts
    'excerptType' => 'lines', // 'lines', 'characters', or 'clean'
])

@php
    // Determine container classes
    $containerClass = $isHighlighted
        ? 'bg-branding-primary/20 dark:bg-branding-primary/10 py-4 px-6 border-b border-essentials-inactive'
        : $containerClasses;

    $viewService = app('App\Services\PostViewService');
@endphp

<div class="{{ $containerClass }}" data-post-item>
    <div>
        <a href="{{ route('posts.show', $post->slug) }}">
            <div class="flex justify-between items-start">
                <div class="flex-col">
                    @if ($showMeta)
                        <div class="flex items-center py-2 space-x-4 text-xs text-gray-900 dark:text-gray-400">
                            <span class="truncate max-w-56">By: {{ $post->user->name ?? 'Unknown' }}</span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                            @if ($viewService->canSeeViewCount($post, auth()->user()))
                                <span>{{ $post->view_count }} views</span>
                            @endif
                        </div>
                    @endif

                    <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400  prose prose-sm max-w-none dark:prose-invert line-clamp-2"
                        style="word-wrap: break-word; overflow-wrap: anywhere; word-break: break-word;">
                        {{ $post->title }}
                    </h3>

                    @if ($showExcerpt)
                        <div class="text-gray-900 dark:text-gray-400 mt-2 prose prose-sm max-w-none dark:prose-invert line-clamp-3"
                            style="word-wrap: break-word; overflow-wrap: anywhere; word-break: break-word;">
                            {!! clean($post->content) !!}
                        </div>
                    @endif
                </div>

                @if ($showFeaturedIcon && $post->is_featured && $post->featured_type !== 'none')
                    <div class="flex items-center">
                        <x-icons.lamp class="h-10 w-10 text-branding-primary" />
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
