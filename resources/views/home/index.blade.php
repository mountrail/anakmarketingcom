<x-app-layout>

    <div class="flex flex-col items-center space-x-2 py-10">

        @auth
            <a href="{{ route('posts.create') }}"
                class="inline-flex items-center my-2 px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Buat Pertanyaan / Diskusi Baru') }}
            </a>
        @endauth
        <form action="{{ route('posts.index') }}" method="GET" class="flex items-center my-2">
            <select name="type" id="post-type-filter" onchange="this.form.submit()"
                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                <option value="question" {{ $selectedType == 'question' ? 'selected' : '' }}>Pertanyaan</option>
                <option value="discussion" {{ $selectedType == 'discussion' ? 'selected' : '' }}>Diskusi
                </option>
            </select>
        </form>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-post-list :selectedType="$selectedType" />
        </div>
    </div>
</x-app-layout>
