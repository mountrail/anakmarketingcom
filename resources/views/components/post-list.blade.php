{{-- Components/post-list.blade.php --}}
<div>
    <!-- Editor's Picks Section -->

    @if ($editorPicks->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($editorPicks as $pick)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <a href="{{ route('posts.show', $pick->id) }}"
                                class="text-lg font-semibold hover:text-orange-500 dark:hover:text-orange-400">
                                {{ $pick->title }}
                            </a>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                            {!! Str::limit(strip_tags($pick->content, '<p><br><b><i><strong><em><span>'), 100, '...') !!}
                        </p>

                        <div class="flex items-center space-x-4 mt-3 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $pick->type === 'question' ? 'Pertanyaan' : 'Diskusi' }}</span>
                            <span>{{ $pick->view_count }} views</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Latest Posts -->
<div>


    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if ($posts->isEmpty())
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <p class="text-center">No
                    {{ $selectedType == 'question' ? 'questions' : 'discussions' }}
                    found.</p>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($posts as $post)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center py-2 space-x-4 text-xs text-gray-500 dark:text-gray-400">
                            <span>By: {{ $post->user->name ?? 'Unknown' }}</span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                            <span>{{ $post->view_count }} views</span>
                        </div>
                        <div class="flex justify-between items-start">
                            <a href="{{ route('posts.show', $post->id) }}"
                                class="text-lg font-medium hover:text-indigo-600 dark:hover:text-indigo-400">
                                {{ $post->title }}
                            </a>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                            {!! Str::limit(strip_tags($post->content, '<b><i><strong><em><span>'), 100, '...') !!}
                        </p>


                        <div class="flex items-center space-x-2 mt-3">
                            <!-- Using the new vote-buttons component -->
                            <x-vote-buttons :model="$post" modelType="post" />

                            <span
                                class="inline-flex items-center text-xs px-1 py-1 rounded-md">
                                <x-icons.comment class="h-4 w-4 mr-1" />
                                {{ $post->answers->count() }}
                            </span>
                            <button
                                class="inline-flex items-center text-xs px-1 py-1 rounded-md">
                                <x-icons.share class="h-4 w-4 mr-1" />
                                Share
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    @endif
</div>
</div>
