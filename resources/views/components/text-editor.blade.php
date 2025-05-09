@props(['disabled' => false, 'value' => ''])

<div>
    <textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
        'class' =>
            'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm',
    ]) !!}>{{ $value }}</textarea>
</div>

@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/tinymce@6/skins/ui/oxide/skin.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: 'textarea{{ $attributes->get('id') ? '#' . $attributes->get('id') : '' }}',
                plugins: 'image lists',
                toolbar: 'bold italic underline | bullist numlist | image',
                menubar: false, // Remove "File Edit View..." bar
                height: 400,
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                entity_encoding: 'raw',
                images_upload_url: '/admin/upload-tinymce-image',
                automatic_uploads: true,
                file_picker_types: 'image',
                images_reuse_filename: true,
                image_title: true,
                file_picker_callback: function(callback, value, meta) {
                    if (meta.filetype === 'image') {
                        const input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/*');
                        input.onchange = function() {
                            const file = this.files[0];
                            const reader = new FileReader();
                            reader.onload = function() {
                                const formData = new FormData();
                                formData.append('file', file);
                                const token = document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content');
                                fetch('/admin/upload-tinymce-image', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token
                                        },
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.location) {
                                            callback(data.location, {
                                                title: file.name
                                            });
                                        } else {
                                            alert('Upload failed: ' + (data.error ||
                                                'Unknown error'));
                                        }
                                    })
                                    .catch(error => {
                                        alert('Image upload failed: ' + error);
                                    });
                            };
                            reader.readAsDataURL(file);
                        };
                        input.click();
                    }
                },
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });

        });
    </script>
@endpush
