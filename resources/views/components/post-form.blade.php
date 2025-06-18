@props([
    'post' => null,
    'action' => '',
    'method' => 'POST',
    'backUrl' => route('home'),
    'submitText' => 'Post',
])

<div class="space-y-6">

    <form method="POST" action="{{ $action }}" class="space-y-6" enctype="multipart/form-data" id="post-form">
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        <!-- Title Field -->
        <div>
            <x-input-label for="title" :value="__('Judul')" class="font-bold text-lg" />
            <x-text-input id="title" name="title" type="text"
                class="mt-1 block w-full border-essentials-inactive dark:bg-essentials-inactive/20 dark:border-essentials-inactive dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                value="{{ old('title', $post->title ?? '') }}" required
                placeholder="Tulis pertanyaan / diskusi utama di sini.." />
            @error('title')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Category Field -->
        <div>
            <x-input-label for="type" :value="__('Kategori')" class="font-bold text-lg" />
            <x-select-input id="type" name="type" :selected="old('type', $post->type ?? 'question')" :options="['question' => 'Pertanyaan', 'discussion' => 'Diskusi']" :showPlaceholder="false"
                class="mt-1 w-48 border-essentials-inactive dark:bg-essentials-inactive/20 dark:border-essentials-inactive dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
            @error('type')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description Field -->
        <div>
            <x-input-label for="content" :value="__('Deskripsi')" />
            @include('posts.partials.tinymce-editor', [
                'name' => 'content',
                'id' => 'content',
                'value' => old('content', $post->content ?? ''),
                'maxchars' => 3300,
            ])
            @error('content')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Image Upload -->
        <div class="relative">
            @php
                $existingImagesData = [];
                if ($post && $post->images) {
                    $existingImagesData = $post->images
                        ->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'url' => $image->url,
                                'name' => $image->name ?: basename($image->url),
                                'isExisting' => true,
                            ];
                        })
                        ->toArray();
                }
                $existingImagesJson = old('uploaded_images', json_encode($existingImagesData));
            @endphp

            @include('posts.partials.image-upload', [
                'name' => 'uploaded_images',
                'existingImages' => $existingImagesJson,
            ])
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end mt-4 gap-3">
            <x-primary-button type="button" variant="inactive" size="md"
                onclick="window.location.href='{{ $backUrl }}'">
                {{ __('Kembali') }}
            </x-primary-button>

            <x-primary-button type="submit" id="submit-btn" variant="primary" size="md">
                {{ __($submitText) }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('post-form');
            const submitBtn = document.getElementById('submit-btn');

            form.addEventListener('submit', function(e) {
                if (form.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                }
            });

            @if ($errors->any())
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            @endif

            window.addEventListener('pageshow', function(e) {
                if (e.persisted) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                }
            });
        });
    </script>
@endpush
