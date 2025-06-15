{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6">
            <div class="bg-white dark:bg-gray-800">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center py-16">
                        <!-- Main Message -->
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            404 - Halaman Tidak Ditemukan
                        </h1>

                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                            Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin halaman tersebut telah dipindahkan
                            atau dihapus.
                        </p>
                        <a href="{{ route('home') }}">
                            <x-primary-button href="{{ url('/') }}">
                                Kembali ke Halaman Utama
                            </x-primary-button>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
