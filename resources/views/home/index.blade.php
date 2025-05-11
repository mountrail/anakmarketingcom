<x-app-layout>

    <div class="flex flex-col items-center space-x-2 py-8">

        <div class="inline-flex items-center my-1 px-4 py-2 ">
            <a href="{{ Auth::check() ? route('posts.create') : '#' }}"
                @if (!Auth::check()) data-auth-action="login" @endif
                class="px-6 py-2 bg-branding-primary text-branding-light rounded-md text-xl font-bold shadow-md">
                {{ __('Mulai Pertanyaan / Diskusi') }}
            </a>
        </div>
        <form action="{{ route('posts.index') }}" method="GET" class="flex items-center my-1  max-w-52 w-full">
            <select name="type" id="post-type-filter" onchange="this.form.submit()"
                class="w-full rounded-md font-bold border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-secondary-pale  focus:ring-secondary-pale shadow-md">
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
