{{-- resources\views\onboarding\discussion-list.blade.php --}}
@extends('layouts.app', ['showSidebar' => false])

@section('content')
    <div class="max-w-2xl mx-auto px-0 sm:px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8 px-2 sm:px-0">
            <h1 class="text-lg font-medium text-gray-800 mb-2">
                Ikuti diskusi atau pertanyaan dengan<br>
                Upvote, Downvote, atau memberikan<br>
                komentar di postingan melalui Home dan<br>
                klik yang membuatmu tertarik
            </h1>
            <p class="text-xl font-bold mt-4">
                Kami bantu untuk yang pertama
            </p>
        </div>

        <!-- Featured Section Header -->
        <div class="mb-6 px-2 sm:px-0">
            <h2 class="text-2xl font-bold text-branding-primary text-center mb-4">
                Diskusi dan Pertanyaan<br>
                Rekomendasi Editor Hari Ini
            </h2>
        </div>

        <!-- Editor's Picks List (No padding on main div, no space between items) -->
        <div class="mb-8">
            @if ($editorPicks->count() > 0)
                @foreach ($editorPicks as $post)
                    <x-post-item :post="$post" :showMeta="true" :showExcerpt="true" :showFeaturedIcon="true" :showActionBar="true"
                        :showVoteScore="false" :showCommentCount="true" :showShare="true" :showThreeDots="false" :isHighlighted="true"
                        :excerptLength="150" containerClasses="" customClasses="text-sm" />
                @endforeach
            @else
                <!-- No Posts Available -->
                <div class="text-center py-8">
                    <div class="text-gray-500 text-lg mb-2">
                        Belum ada diskusi atau pertanyaan yang tersedia
                    </div>
                    <p class="text-gray-400 text-sm">
                        Coba lagi nanti atau mulai diskusi pertamamu!
                    </p>
                </div>
            @endif
        </div>

        <!-- Bottom Navigation Buttons -->
        <div class="flex space-x-3 px-2 sm:px-0">
            <!-- Main Home Button -->
            <x-primary-button onclick="window.location.href='{{ route('home') }}'" variant="dark" size="sm"
                class="flex-1">
                ke Halaman Utama
            </x-primary-button>

            <!-- Back to Checklist Button -->
            <x-primary-button onclick="window.location.href='{{ route('onboarding.checklist') }}'" variant="primary"
                size="sm" class="flex-1">
                Kembali ke Checklist
            </x-primary-button>
        </div>
    </div>
@endsection
