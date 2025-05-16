{{-- resources/views/posts/partials/image-gallery.blade.php --}}
@props(['images'])

@if (!empty($images) && count($images) > 0)
    <div class="my-6">
        @php
            $count = count($images);
        @endphp

        <div id="image-gallery" class="overflow-hidden rounded-lg">
            @if ($count === 1)
                {{-- Single image takes full width --}}
                <div class="relative w-full">
                    <img src="{{ $images[0]->url }}" alt="{{ $images[0]->name }}"
                        class="w-full h-auto cursor-pointer object-cover"
                        onclick="openLightbox({{ json_encode($images) }}, 0)">
                </div>
            @elseif($count === 2)
                {{-- Two images in two columns --}}
                <div class="grid grid-cols-2 gap-1">
                    @foreach ($images as $index => $image)
                        <div class="relative">
                            <img src="{{ $image->url }}" alt="{{ $image->name }}"
                                class="w-full h-48 cursor-pointer object-cover"
                                onclick="openLightbox({{ json_encode($images) }}, {{ $index }})">
                        </div>
                    @endforeach
                </div>
            @elseif($count === 3)
                {{-- Two images on top, one on bottom --}}
                <div class="grid grid-cols-2 gap-1">
                    @foreach ($images->take(2) as $index => $image)
                        <div class="relative">
                            <img src="{{ $image->url }}" alt="{{ $image->name }}"
                                class="w-full h-48 cursor-pointer object-cover"
                                onclick="openLightbox({{ json_encode($images) }}, {{ $index }})">
                        </div>
                    @endforeach
                </div>
                <div class="mt-1">
                    <img src="{{ $images[2]->url }}" alt="{{ $images[2]->name }}"
                        class="w-full h-auto cursor-pointer object-cover"
                        onclick="openLightbox({{ json_encode($images) }}, 2)">
                </div>
            @elseif($count === 4)
                {{-- Four images in 2x2 grid --}}
                <div class="grid grid-cols-2 gap-1">
                    @foreach ($images as $index => $image)
                        <div class="relative">
                            <img src="{{ $image->url }}" alt="{{ $image->name }}"
                                class="w-full h-48 cursor-pointer object-cover"
                                onclick="openLightbox({{ json_encode($images) }}, {{ $index }})">
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Five or more images: 2+2+1 layout, with the last showing a count --}}
                <div class="grid grid-cols-2 gap-1">
                    @foreach ($images->take(4) as $index => $image)
                        <div class="relative">
                            <img src="{{ $image->url }}" alt="{{ $image->name }}"
                                class="w-full h-48 cursor-pointer object-cover"
                                onclick="openLightbox({{ json_encode($images) }}, {{ $index }})">
                        </div>
                    @endforeach
                </div>
                <div class="mt-1 relative">
                    <img src="{{ $images[4]->url }}" alt="{{ $images[4]->name }}"
                        class="w-full h-48 cursor-pointer object-cover"
                        onclick="openLightbox({{ json_encode($images) }}, 4)">
                    @if ($count > 5)
                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-60 cursor-pointer"
                            onclick="openLightbox({{ json_encode($images) }}, 4)">
                            <span class="text-white text-xl font-bold">+{{ $count - 5 }} more</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Lightbox Modal --}}
    <div id="lightbox-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-90 flex items-center justify-center">
        <div class="max-w-4xl w-full p-4">
            <div class="relative">
                <button type="button" class="absolute top-4 right-4 text-white z-10" onclick="closeLightbox()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div id="lightbox-content" class="flex flex-col items-center">
                    <img id="lightbox-image" src="" alt="" class="max-h-[80vh] max-w-full">
                    <div class="flex justify-between w-full mt-4">
                        <button type="button" class="text-white p-2" onclick="prevImage()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="text-white text-center">
                            <span id="current-image-index">1</span> / <span
                                id="total-images">{{ $count }}</span>
                        </div>
                        <button type="button" class="text-white p-2" onclick="nextImage()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let lightboxImages = [];
        let currentImageIndex = 0;

        function openLightbox(images, index) {
            lightboxImages = images;
            currentImageIndex = index;

            document.getElementById('lightbox-modal').classList.remove('hidden');
            document.getElementById('lightbox-modal').classList.add('flex');
            document.body.style.overflow = 'hidden';

            updateLightboxImage();
        }

        function closeLightbox() {
            document.getElementById('lightbox-modal').classList.add('hidden');
            document.getElementById('lightbox-modal').classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function updateLightboxImage() {
            const image = lightboxImages[currentImageIndex];
            document.getElementById('lightbox-image').src = image.url;
            document.getElementById('lightbox-image').alt = image.name || '';
            document.getElementById('current-image-index').textContent = currentImageIndex + 1;
            document.getElementById('total-images').textContent = lightboxImages.length;
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % lightboxImages.length;
            updateLightboxImage();
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + lightboxImages.length) % lightboxImages.length;
            updateLightboxImage();
        }

        // Close lightbox with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            } else if (e.key === 'ArrowRight') {
                nextImage();
            } else if (e.key === 'ArrowLeft') {
                prevImage();
            }
        });

        // Close lightbox when clicking outside of the image
        document.getElementById('lightbox-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
    </script>
@endif
