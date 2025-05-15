{{-- resources/views/posts/partials/answer-form.blade.php --}}

<div class="my-8 border-y py-6">
    <h3 class="text-xl font-semibold mb-4">Your Answer</h3>

    @auth
        <form method="POST" action="{{ route('posts.answers.store', $post->id) }}" class="space-y-4">
            @csrf

            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Write your answer
                </label>
                <textarea name="content" id="content" rows="5"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
                    placeholder="Share your knowledge or experience here..." required>{{ old('content') }}</textarea>

                @error('content')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-branding-primary dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Post Answer
                </button>
            </div>
        </form>
    @else
        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-md">
            <p class="text-gray-600 dark:text-gray-300">
                Please <a data-auth-action="login" class="text-indigo-600 dark:text-indigo-400 hover:underline">log
                    in</a>
                or <a  data-auth-action="register"
                    class="text-indigo-600 dark:text-indigo-400 hover:underline">register</a>
                to post an answer.
            </p>
        </div>
    @endauth
</div>
