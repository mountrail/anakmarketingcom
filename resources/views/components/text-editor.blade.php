@props(['disabled' => false, 'value' => ''])

<div>
    <textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
        'class' =>
            'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm',
    ]) !!}>{{ $value }}</textarea>
    <div id="upload-status" class="mt-2 text-sm"></div>
</div>

@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/tinymce@6/skins/ui/oxide/skin.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/tinymce@6/skins/content/default/content.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const uploadStatus = document.getElementById('upload-status');

            tinymce.init({
                selector: 'textarea{{ $attributes->get('id') ? '#' . $attributes->get('id') : '' }}',
                plugins: 'image lists',
                toolbar: 'bold italic underline | bullist numlist | image',
                menubar: false,
                height: 400,
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                entity_encoding: 'raw',
                branding: false,
                promotion: false,

                // Change default tab to "upload" when inserting image
                file_picker_types: 'image',
                images_upload_url: '{{ route('tinymce.upload') }}',

                // Set default tab to file browser when clicking image button
                file_picker_callback: function(callback, value, meta) {
                    // Open file browser directly
                    let input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');

                    input.onchange = function() {
                        if (this.files && this.files[0]) {
                            uploadStatus.innerHTML = 'Preparing image...';

                            let file = this.files[0];
                            let formData = new FormData();
                            formData.append('file', file);

                            fetch('{{ route('tinymce.upload') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.location) {
                                        callback(data.location, {
                                            title: file.name,
                                            alt: file.name
                                        });
                                        uploadStatus.innerHTML = 'Upload successful!';
                                    } else {
                                        uploadStatus.innerHTML = 'Upload error: ' + (data
                                            .error || 'Unknown error');
                                    }
                                })
                                .catch(error => {
                                    uploadStatus.innerHTML = 'Upload error: ' + error;
                                });
                        }
                    };

                    input.click();
                },

                // Keep the standard image upload handler as fallback
                images_upload_handler: function(blobInfo, progress) {
                    return new Promise((resolve, reject) => {
                        uploadStatus.innerHTML = 'Upload starting...';

                        const xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;
                        xhr.open('POST', '{{ route('tinymce.upload') }}');

                        // Important: Set CSRF token in header
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                        xhr.upload.onprogress = function(e) {
                            progress(e.loaded / e.total * 100);
                            uploadStatus.innerHTML = 'Uploading: ' + Math.round(e.loaded / e
                                .total * 100) + '%';
                        };

                        xhr.onload = function() {
                            if (xhr.status === 403) {
                                uploadStatus.innerHTML = 'Error: Forbidden (403)';
                                reject({
                                    message: 'HTTP Error: ' + xhr.status,
                                    remove: true
                                });
                                return;
                            }

                            if (xhr.status < 200 || xhr.status >= 300) {
                                uploadStatus.innerHTML = 'Error: HTTP ' + xhr.status;
                                reject('HTTP Error: ' + xhr.status);
                                return;
                            }

                            // Debug response
                            console.log('Server response:', xhr.responseText);

                            try {
                                const json = JSON.parse(xhr.responseText);

                                if (!json || typeof json.location != 'string') {
                                    uploadStatus.innerHTML = 'Error: Invalid response';
                                    reject('Invalid JSON: ' + xhr.responseText);
                                    return;
                                }

                                uploadStatus.innerHTML = 'Upload successful! Image at: ' +
                                    json.location;
                                console.log('Image uploaded to:', json.location);
                                resolve(json.location);
                            } catch (e) {
                                uploadStatus.innerHTML = 'Error parsing response: ' + e
                                    .message;
                                reject('Error parsing response: ' + e.message);
                            }
                        };

                        xhr.onerror = function() {
                            uploadStatus.innerHTML = 'Network error';
                            reject('Image upload failed due to a network error');
                        };

                        const formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());

                        xhr.send(formData);
                    });
                },

                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });

                    // This ensures the content is properly initialized with any existing value
                    editor.on('init', function() {
                        editor.setContent(editor.getElement().value);
                    });
                }
            });
        });
    </script>
@endpush
