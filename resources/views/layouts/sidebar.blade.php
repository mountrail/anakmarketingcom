{{-- resources\views\layouts\sidebar.blade.php --}}
@if ($editorPicks->count() > 0)
    <div class="hidden lg:block w-1/3 mt-20">
        <div class="bg-white dark:bg-gray-800 p-4">
            <h3 class="text-xl font-bold mb-4 text-branding-primary dark:text-gray-200">Pilihan Editor</h3>

            <div>
                @foreach ($editorPicks as $pick)
                    <x-post-item :post="$pick" :showMeta="false" :showVoteScore="false" :showCommentCount="true"
                        :showShare="true" :showThreeDots="false" customClasses="text-xs"
                        containerClasses="p-4 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0" />
                @endforeach
            </div>
        </div>
    </div>
@endif
