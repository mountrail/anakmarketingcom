{{-- resources\views\layouts\sidebar.blade.php --}}
<div class="hidden lg:block w-1/3 mt-20">
    <div class="bg-white dark:bg-gray-800 p-4">
        <h3 class="text-xl font-bold mb-4 text-branding-primary dark:text-gray-200">Editor's Picks</h3>

        @if ($editorPicks->count() > 0)
            <div class="space-y-4 ml-6">
                @foreach ($editorPicks as $pick)
                    <x-post-item :post="$pick" :showMeta="false" :showVoteScore="false" :showCommentCount="true"
                        :showShare="true" :showThreeDots="false" customClasses="text-xs"
                        containerClasses="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0" />
                @endforeach
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No editor picks available</p>
        @endif
    </div>
</div>
