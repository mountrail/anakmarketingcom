{{-- resources/views/posts/partials/answer-form.blade.php --}}

<div class="my-8 border-y py-6">
    <h3 class="text-xl font-semibold mb-4">Your Answer</h3>

    @auth
        <div class="flex space-x-4">
            <!-- User Profile Photo -->
            <div class="flex-shrink-0">
                <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->getProfileImageUrl() }}"
                    alt="{{ Auth::user()->name }}">
            </div>

            <!-- Form Container -->
            <div class="flex-1">
                <form method="POST" action="{{ route('posts.answers.store', $post->id) }}">
                    @csrf

                    <div>
                        <label for="content"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 sr-only">
                            Write your answer
                        </label>
                        <textarea name="content" id="content" rows="5"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm resize-none"
                            placeholder="Write your comment.." required>{{ old('content') }}</textarea>

                        @error('content')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button type="submit" variant="primary" size="lg">
                            Post
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-md">
            <p class="text-gray-600 dark:text-gray-300">
                Please <a data-auth-action="login" class="text-indigo-600 dark:text-indigo-400 hover:underline">log
                    in</a>
                or <a data-auth-action="register" class="text-indigo-600 dark:text-indigo-400 hover:underline">register</a>
                to post an answer.
            </p>
        </div>
    @endauth
</div>
