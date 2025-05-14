{{-- home/partials/post-list.blade.php --}}
<div>
    <div class="flex flex-col items-center space-x-2 mt-4">
        <div class="inline-flex items-center my-1 px-4 py-2 ">
            <a href="{{ Auth::check() ? route('posts.create') : '#' }}"
                @if (!Auth::check()) data-auth-action="login" @endif
                class="px-6 py-2 bg-branding-primary text-branding-light rounded-md text-xl font-bold shadow-md">
                {{ __('Mulai Pertanyaan / Diskusi') }}
            </a>
        </div>

        <div class="flex items-center my-1">
            <x-dropdown align="center" width="64">
                <x-slot name="trigger">
                    <button
                        class="flex items-center w-52 rounded-md font-bold px-5 py-2.5 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:border-secondary-pale focus:ring-secondary-pale shadow-md">
                        <span class="text-base">{{ $selectedType == 'question' ? 'Pertanyaan' : 'Diskusi' }}</span>
                        <svg class="ms-auto h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="py-1">
                        <a href="{{ route('posts.index', ['type' => 'question']) }}"
                            class="block w-full px-5 py-3 text-base font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                            Pertanyaan
                        </a>
                        <a href="{{ route('posts.index', ['type' => 'discussion']) }}"
                            class="block w-full px-5 py-3 text-base font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                            Diskusi
                        </a>
                    </div>
                </x-slot>
            </x-dropdown>
        </div>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto">
            <!-- Editor's Picks Section -->
            @if ($editorPicks->count() > 0)
                <div>
                    @foreach ($editorPicks as $pick)
                        <div
                            class="bg-branding-primary/20 dark:bg-branding-primary/10 border-b border-essentials-inactive">
                            <div class="p-4">
                                <div class="flex items-center py-2 space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                    <span>By: {{ $pick->user->name ?? 'Unknown' }}</span>
                                    <span>{{ $pick->created_at->diffForHumans() }}</span>
                                    <span>{{ $pick->view_count }} views</span>
                                </div>
                                <div class="flex justify-between items-start">
                                    <a href="{{ route('posts.show', $pick->id) }}"
                                        class="text-lg font-medium hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $pick->title }}
                                    </a>
                                    <div class="flex items-center">
                                        <x-icons.lamp class="h-12 w-12 text-orange-500" />
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    {!! Str::limit(strip_tags($pick->content, '<p><br><b><i><strong><em><span>'), 100, '...') !!}
                                </p>

                                <div class="flex items-center mt-3 relative">
                                    <!-- Using the new action-bar component -->
                                    <x-action-bar :model="$pick" modelType="post" :showVoteScore="false" :showCommentCount="true"
                                        :showShare="true" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Latest Posts -->
            <div>
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if ($posts->isEmpty())
                    <div class="bg-white dark:bg-gray-800 border-b border-essentials-inactive">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <p class="text-center">No
                                {{ $selectedType == 'question' ? 'questions' : 'discussions' }}
                                found.</p>
                        </div>
                    </div>
                @else
                    <div>
                        @foreach ($posts as $post)
                            <div class="bg-white dark:bg-gray-800 border-b border-essentials-inactive">
                                <div class="p-4">
                                    <div
                                        class="flex items-center py-2 space-x-4 text-xs text-gray-500 dark:text-gray-400">
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

                                    <div class="flex items-center mt-3 relative">
                                        <!-- Using the new action-bar component -->
                                        <x-action-bar :model="$post" modelType="post" :showVoteScore="false"
                                            :showCommentCount="true" :showShare="true" />
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
    </div>
</div>
