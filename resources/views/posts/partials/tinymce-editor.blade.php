{{-- resources/views/posts/partials/tinymce-editor.blade.php --}}
@props(['name' => 'content', 'id' => 'content', 'value' => '', 'maxchars' => 3300, 'disabled' => false])

<div class="tinymce-editor-container">
    <textarea
        {{ $disabled ? 'disabled' : '' }}
        name="{{ $name }}"
        id="{{ $id }}"
        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full"
    >{{ $value }}</textarea>

    <div class="character-count mt-2 text-sm flex justify-between">
        <span class="text-gray-600 dark:text-gray-400">
            Karakter: <span class="current-count">0</span>/<span class="max-count">{{ $maxchars }}</span>
        </span>
        <span class="chars-remaining text-gray-600 dark:text-gray-400">
            Tersisa: <span class="remaining-count">{{ $maxchars }}</span>
        </span>
    </div>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editorId = '{{ $id }}';
            const maxChars = {{ $maxchars }};
            const editorContainer = document.querySelector('#' + editorId).closest('.tinymce-editor-container');
            const currentCount = editorContainer.querySelector('.current-count');
            const remainingCount = editorContainer.querySelector('.remaining-count');
            const charsRemaining = editorContainer.querySelector('.chars-remaining');

            // Function to update character count
            function updateCharacterCount(count) {
                currentCount.textContent = count;
                const remaining = maxChars - count;
                remainingCount.textContent = remaining;

                // Change color based on remaining chars
                if (remaining < 0) {
                    charsRemaining.className = 'chars-remaining text-red-500 dark:text-red-400 font-medium';
                } else if (remaining < maxChars * 0.1) { // Less than 10% remaining
                    charsRemaining.className = 'chars-remaining text-orange-500 dark:text-orange-400';
                } else {
                    charsRemaining.className = 'chars-remaining text-gray-600 dark:text-gray-400';
                }
            }

            tinymce.init({
                selector: '#' + editorId,
                plugins: 'lists wordcount',
                toolbar: 'bold italic underline | bullist',
                menubar: false,
                height: 300,
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                entity_encoding: 'raw',
                branding: false,
                promotion: false,
                convert_urls: false,
                relative_urls: false,
                remove_script_host: false,
                max_chars: maxChars,

                // Disable image functionality
                paste_data_images: false,
                automatic_uploads: false,
                images_upload_handler: function() {
                    return false; // Prevents default image upload
                },

                // Character count settings
                wordcount_countcharacters: true,

                setup: function(editor) {
                    editor.on('change keyup paste input', function() {
                        editor.save();
                        const text = editor.getContent({
                            format: 'text'
                        });
                        const count = text.length;
                        updateCharacterCount(count);
                    });

                    // Prevent image drag and drop
                    editor.on('dragover drop', function(e) {
                        const hasImageFile = e.dataTransfer && e.dataTransfer.types &&
                            e.dataTransfer.types.indexOf('Files') !== -1;
                        if (hasImageFile) {
                            e.preventDefault();
                            return false;
                        }
                    });

                    // Handle paste to remove any images
                    editor.on('PastePreProcess', function(e) {
                        // Remove any img tags from pasted content
                        e.content = e.content.replace(/<img[^>]*>/g, '');
                    });

                    // Initialize with existing content
                    editor.on('init', function() {
                        editor.setContent(editor.getElement().value);
                        const text = editor.getContent({
                            format: 'text'
                        });
                        const count = text.length;
                        updateCharacterCount(count);
                    });
                }
            });
        });
    </script>
@endpush
