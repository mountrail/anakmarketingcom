@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold">{{ $post->title }}</h1>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <span>{{ $post->type === 'question' ? 'Pertanyaan' : 'Diskusi' }}</span>
                                <span>Oleh: {{ $post->user->name }}</span>
                                <span>{{ $post->created_at->format('d M Y H:i') }}</span>
                                <span>{{ $post->view_count }} views</span>
                            </div>
                        </div>


                    </div>

                    <div class="mt-6 border-t pt-6">
                        <div class="prose dark:prose-invert max-w-none">
                            {!! $post->content !!}
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('home') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:bg-gray-400 dark:focus:bg-gray-500 active:bg-gray-500 dark:active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
