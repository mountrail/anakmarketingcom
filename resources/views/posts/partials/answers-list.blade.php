@if ($post->answers->count() > 0)
    <div class="space-y-8">
        @foreach ($post->answers->sortByDesc('is_editors_pick')->sortByDesc('created_at') as $answer)
            <div class="border-b pb-6 last:border-b-0" id="answer-{{ $answer->id }}">
                <!-- Answer Header - Profile, User Info, and Badges -->
                <div class="flex justify-between items-start mb-4">
                    <!-- Left side: Profile Picture and User Info -->
                    <div class="flex items-start space-x-3">
                        <!-- Profile Picture -->
                        <img src="{{ $answer->user->getProfileImageUrl() }}" alt="{{ $answer->user->name }}"
                            class="h-10 w-10 rounded-full object-cover">

                        <!-- Name and Time -->
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $answer->user->name }}</span>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400">{{ $answer->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <!-- Right side: Badges and Editor's Pick -->
                    <div class="flex items-center space-x-2">
                        @if ($answer->is_editors_pick)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                Editor's Pick
                            </span>
                        @endif

                        <!-- User Badges -->
                        <div class="flex items-center space-x-1">
                            <span><x-icons.badge class="w-5 h-5" /></span>
                            <span><x-icons.badge class="w-5 h-5" /></span>
                            <span><x-icons.badge class="w-5 h-5" /></span>
                        </div>
                    </div>
                </div>

                <!-- Answer Content -->
                <div class="mt-3 prose dark:prose-invert max-w-none">
                    {!! $answer->content !!}
                </div>

                <!-- Action Bar -->
                <div class="mt-4">
                    <x-action-bar :model="$answer" modelType="answer" :showVoteScore="false" :showCommentCount="false"
                        :showShare="false" customClasses="justify-start" />
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="p-6 text-center border rounded-md border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
        <p class="text-gray-500 dark:text-gray-400">
            No answers yet. Be the first to share your knowledge!
        </p>
    </div>
@endif
