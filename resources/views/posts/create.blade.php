{{-- resources\views\posts\create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Pertanyaan / Diskusi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('posts.store') }}" class="space-y-6" enctype="multipart/form-data"
                        id="post-form">
                        @csrf

                        <div>
                            <x-input-label for="title" :value="__('Judul')" class="font-bold text-lg" />
                            <x-text-input id="title" name="title" type="text"
                                class="mt-1 block w-full border-essentials-inactive dark:bg-essentials-inactive/20 dark:border-essentials-inactive dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                value="{{ old('title') }}" required
                                placeholder="Tulis pertanyaan / diskusi utama di sini.." />
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Kategori')" class="font-bold text-lg" />
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button type="button"
                                        class="flex items-center w-48 rounded-md font-medium px-3 py-2 mt-1 border border-essentials-inactive dark:bg-essentials-inactive/20 dark:border-essentials-inactive text-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                                        <span
                                            id="selected-type-text">{{ old('type') == 'discussion' ? 'Diskusi' : 'Pertanyaan' }}</span>
                                        <svg class="ms-auto h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="py-1">
                                        <button type="button" data-value="question"
                                            class="dropdown-option block w-full px-3 py-2 text-base font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out border-b border-gray-200 dark:border-gray-600 {{ old('type', 'question') == 'question' ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                                            Pertanyaan
                                        </button>
                                        <button type="button" data-value="discussion"
                                            class="dropdown-option block w-full px-3 py-2 text-base font-medium text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out {{ old('type') == 'discussion' ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                                            Diskusi
                                        </button>
                                    </div>
                                </x-slot>
                            </x-dropdown>

                            <!-- Hidden input to store the selected value -->
                            <input type="hidden" name="type" id="type" value="{{ old('type', 'question') }}">

                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="content" :value="__('Deskripsi')" class="font-bold text-lg" />
                            <x-text-editor id="content" name="content" :value="old('content')"
                                class="bg-essentials-inactive/20 border-essentials-inactive dark:bg-essentials-inactive/20 dark:border-essentials-inactive" />
                            @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4 gap-3">
                            <x-primary-button type="button" variant="inactive" size="md"
                                onclick="window.location.href='{{ route('home') }}'">
                                {{ __('Kembali') }}
                            </x-primary-button>

                            <x-primary-button type="submit" id="submit-btn" variant="primary" size="md">
                                {{ __('Post') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle dropdown selection
                const dropdownOptions = document.querySelectorAll('.dropdown-option');
                const selectedTypeText = document.getElementById('selected-type-text');
                const hiddenTypeInput = document.getElementById('type');

                dropdownOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        const value = this.getAttribute('data-value');
                        const text = this.textContent.trim();

                        hiddenTypeInput.value = value;
                        selectedTypeText.textContent = text;

                        dropdownOptions.forEach(opt => {
                            opt.classList.remove('bg-gray-100', 'dark:bg-gray-800');
                        });
                        this.classList.add('bg-gray-100', 'dark:bg-gray-800');

                        const dropdown = this.closest('[data-dropdown]') || this.closest('.relative');
                        if (dropdown) {
                            const trigger = dropdown.querySelector('button[data-dropdown-toggle]');
                            if (trigger) {
                                trigger.click();
                            }
                        }
                    });
                });

                // Simple form submission
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
</x-app-layout>
