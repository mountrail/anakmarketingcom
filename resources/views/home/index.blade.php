<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Home') }}
            </h2>

            <div class="flex items-center space-x-2">
                <form action="{{ route('home') }}" method="GET" class="flex items-center">
                    <select name="type" id="post-type-filter" onchange="this.form.submit()"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                        <option value="question" {{ $selectedType == 'question' ? 'selected' : '' }}>Pertanyaan</option>
                        <option value="discussion" {{ $selectedType == 'discussion' ? 'selected' : '' }}>Diskusi
                        </option>
                    </select>
                </form>

                @auth
                    <a href="{{ route('posts.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('Buat Pertanyaan / Diskusi Baru') }}
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('Login to Post') }}
                    </a>
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-4">Welcome to Anak Marketing!</h1>
                    <p class="mb-4">This is our community platform for marketing professionals and enthusiasts.</p>

                    @guest
                        <div class="mt-6">
                            <p class="mb-4">To post questions or participate in discussions, please log in or create an
                                account.</p>
                        </div>
                    @else
                        <div class="mt-6">
                            <p>You're logged in as <strong>{{ Auth::user()->name }}</strong>!</p>
                        </div>
                    @endguest
                </div>
            </div>

            <!-- Editor's Picks Section -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-orange-500 dark:text-orange-400 mb-4">Editor's Picks</h2>

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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                        {!! Str::limit(strip_tags($post->content, '<b><i><u><strong><em><span>'), 100) !!}
                                    </p>


                                    <div
                                        class="flex items-center space-x-4 mt-3 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $pick->type === 'question' ? 'Pertanyaan' : 'Diskusi' }}</span>
                                        <span>{{ $pick->view_count }} views</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400">No featured posts yet.</p>
                @endif
            </div>

            <!-- Latest Posts -->
            <div>
                <h2 class="text-xl font-bold mb-4">
                    {{ $selectedType == 'question' ? 'Latest Questions' : 'Latest Discussions' }}</h2>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if ($posts->isEmpty())
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <p class="text-center">No {{ $selectedType == 'question' ? 'questions' : 'discussions' }}
                                found.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($posts as $post)
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-4">
                                    <div class="flex justify-between items-start">
                                        <a href="{{ route('posts.show', $post->id) }}"
                                            class="text-lg font-medium hover:text-indigo-600 dark:hover:text-indigo-400">
                                            {{ $post->title }}
                                        </a>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                        {!! Str::limit(strip_tags($post->content, '<b><i><u><strong><em><span>'), 100) !!}
                                    </p>


                                    <div class="flex items-center justify-between mt-3">
                                        <div
                                            class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span>By: {{ $post->user->name ?? 'Unknown' }}</span>
                                            <span>{{ $post->created_at->diffForHumans() }}</span>
                                            <span>{{ $post->view_count }} views</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button
                                                class="inline-flex items-center text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded-md">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                                Upvote
                                            </button>
                                            <button
                                                class="inline-flex items-center text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded-md">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                                Downvote
                                            </button>
                                            <span
                                                class="inline-flex items-center text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded-md">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                                </svg>
                                                310
                                            </span>
                                            <button
                                                class="inline-flex items-center text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded-md">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                </svg>
                                                Share
                                            </button>
                                        </div>
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
</x-app-layout>
