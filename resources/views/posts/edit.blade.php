{{-- resources/views/posts/edit.blade.php --}}
{{-- resources/views/posts/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Pertanyaan / Diskusi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <x-post-form :post="$post" :action="route('posts.update', $post)" method="PUT" :back-url="route('posts.show', $post->slug)"
                        submit-text="Update" />
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Category dropdown functionality
                const dropdownOptions = document.querySelectorAll('.dropdown-option');
                const selectedTypeText = document.getElementById('selected-type-text');
                const typeInput = document.getElementById('type');

                dropdownOptions.forEach(option => {
                    option.addEventListener('click', function(e) {
                        e.preventDefault();

                        const value = this.getAttribute('data-value');
                        const text = this.textContent.trim();

                        // Update hidden input
                        typeInput.value = value;

                        // Update display text
                        selectedTypeText.textContent = text;

                        // Update active state styling
                        dropdownOptions.forEach(opt => {
                            opt.classList.remove('bg-gray-100', 'dark:bg-gray-800');
                        });
                        this.classList.add('bg-gray-100', 'dark:bg-gray-800');

                        // Close dropdown by clicking elsewhere or implement your dropdown close logic
                        const dropdown = this.closest('[x-data]');
                        if (dropdown && dropdown.__x) {
                            dropdown.__x.$data.open = false;
                        }
                    });
                });

                // Existing form submission code
                const form = document.getElementById('post-form');
                const submitBtn = document.getElementById('submit-btn');

                form.addEventListener('submit', function(e) {
                    if (form.checkValidity()) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    }
                });

                // Reset button state if there are validation errors
                @if ($errors->any())
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                @endif

                // Handle back/forward navigation
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
