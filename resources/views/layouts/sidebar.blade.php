{{-- home/partials/sidebar-editor-picks.blade.php --}}
<div class="hidden lg:block w-80 ml-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-200">Editor's Picks</h3>

        @if ($editorPicks->count() > 0)
            <div class="space-y-4">
                @foreach ($editorPicks as $pick)
                    <a href="{{ route('posts.show', $pick->id) }}" class="block">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0">
                            <div class="flex items-start">
                                <div class="flex-grow">
                                    <h4
                                        class="font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $pick->title }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                        {!! Str::limit(strip_tags($pick->content, '<b><i><strong><em><span>'), 75, '...') !!}
                                    </p>
                                </div>
                                <div class="ml-2 flex-shrink-0">
                                    <x-icons.lightbulb class="h-5 w-5 text-amber-500" />
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center">
                                    <x-icons.thumb-up class="h-3.5 w-3.5 mr-1" />
                                    {{ $pick->votes_sum_value ?? 0 }}
                                </span>
                                <span class="inline-flex items-center">
                                    <x-icons.comment class="h-3.5 w-3.5 mr-1" />
                                    {{ $pick->answers->count() }}
                                </span>
                                <span class="inline-flex items-center">
                                    <x-icons.share class="h-3.5 w-3.5 mr-1" />
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No editor picks available</p>
        @endif
    </div>
</div>
