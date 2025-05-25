{{-- resources/views/profile/partials/user-posts.blade.php --}}
@props(['user', 'currentPostId' => null, 'limit' => 2, 'showToGuestOnly' => false, 'isOwner' => false])

@php
    // Get user's posts excluding the current post if provided
$postsQuery = $user->posts()->withCount('answers')->latest();

if ($currentPostId) {
    $postsQuery->where('id', '!=', $currentPostId);
    }

    $totalPosts = $postsQuery->count();
    $posts = $postsQuery->take($limit)->get();

    // Check if we should show this section based on showToGuestOnly flag
    $shouldShow = !$showToGuestOnly || ($showToGuestOnly && !$isOwner);
@endphp

@if ($shouldShow)
    <div>
        <h2
            class="font-semibold text-branding-black dark:text-white text-center border-b border-gray-200 dark:border-gray-600 mb-4">
            Pertanyaan/Diskusi
        </h2>

        @if ($posts->count() > 0)
            <div class="space-y-4">
                @foreach ($posts as $post)
                    <x-post-item :post="$post" :showMeta="false" :showVoteScore="false" :showCommentCount="true"
                        :showShare="true" :showThreeDots="false" customClasses="text-xs"
                        containerClasses="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0" />
                @endforeach
            </div>

            @if ($totalPosts > $limit)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
                    @if ($currentPostId)
                        <x-primary-button id="load-more-posts" data-user-id="{{ $user->id }}"
                            data-loaded="{{ $limit }}" data-total="{{ $totalPosts }}"
                            data-current-post-id="{{ $currentPostId }}" variant="primary" size="xl">
                            Show All ({{ $totalPosts }} Posts)
                        </x-primary-button>
                    @else
                        <x-primary-button id="load-more-posts" data-user-id="{{ $user->id }}"
                            data-loaded="{{ $limit }}" data-total="{{ $totalPosts }}" variant="primary"
                            size="xl">
                            Show All ({{ $totalPosts }} Posts)
                        </x-primary-button>
                    @endif
                </div>
            @endif
        @else
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No posts available</p>
        @endif
    </div>
@endif
