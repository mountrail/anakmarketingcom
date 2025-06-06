{{-- resources/views/posts/partials/image-gallery.blade.php --}}
@props(['images'])

@if (!empty($images) && count($images) > 0)
    <div class="my-6">
        @php
            $count = count($images);
        @endphp

        <div id="image-gallery" class="adaptive-gallery">
            @foreach ($images as $index => $image)
                <div class="gallery-item" data-index="{{ $index }}">
                    <img src="{{ $image->url }}" alt="{{ $image->name }}" class="gallery-image cursor-pointer"
                        onclick="openLightbox({{ json_encode($images) }}, {{ $index }})" loading="lazy">
                </div>
            @endforeach
        </div>
    </div>

    {{-- Lightbox Modal --}}
    <div id="lightbox-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-90 flex items-center justify-center">
        <!-- Close button positioned at top-right corner of the viewport -->
        <button type="button"
            class="fixed top-4 right-4 text-white z-50 p-2 rounded-full bg-black bg-opacity-50 hover:bg-opacity-70 transition-all duration-200"
            onclick="closeLightbox()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="max-w-5xl w-full h-full flex flex-col p-4">
            <!-- Image container - constrained height to ensure navigation stays visible -->
            <div class="flex-1 flex items-center justify-center min-h-0" style="max-height: calc(100vh - 120px);">
                <img id="lightbox-image" src="" alt="" class="max-h-full max-w-full object-contain">
            </div>

            <!-- Navigation controls at bottom - always visible -->
            <div class="flex justify-between items-center mt-4 py-4 flex-shrink-0">
                <button type="button"
                    class="text-white p-3 rounded-full bg-black bg-opacity-60 hover:bg-opacity-80 transition-all duration-200 flex items-center justify-center backdrop-blur-sm"
                    onclick="prevImage()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="text-white text-center">
                    <div class="text-lg font-medium bg-black bg-opacity-40 px-4 py-2 rounded-full backdrop-blur-sm">
                        <span id="current-image-index">1</span> / <span id="total-images">{{ $count }}</span>
                    </div>
                </div>

                <button type="button"
                    class="text-white p-3 rounded-full bg-black bg-opacity-60 hover:bg-opacity-80 transition-all duration-200 flex items-center justify-center backdrop-blur-sm"
                    onclick="nextImage()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Adaptive Gallery Styles - Row-based layout */
        .adaptive-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
            justify-content: flex-start;
        }

        .gallery-item {
            flex-grow: 1;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #f8f9fa;
            min-width: 0;
            /* Important for flex item shrinking */
        }

        .gallery-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .gallery-image {
            width: 100%;
            height: 200px;
            /* Base height - will be adjusted by JS */
            display: block;
            object-fit: cover;
            object-position: center;
            transition: transform 0.3s ease;
        }

        .gallery-image:hover {
            transform: scale(1.03);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .adaptive-gallery {
                gap: 6px;
            }

            .gallery-image {
                height: 150px;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .gallery-image {
                height: 180px;
            }
        }

        @media (min-width: 1025px) {
            .gallery-image {
                height: 220px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .gallery-item {
                box-shadow: 0 2px 8px rgba(255, 255, 255, 0.1);
                background: #374151;
            }

            .gallery-item:hover {
                box-shadow: 0 6px 20px rgba(255, 255, 255, 0.15);
            }
        }

        /* Force new row for better layout */
        .gallery-item:nth-child(4n) {
            flex-basis: 100%;
        }

        @media (max-width: 640px) {
            .gallery-item:nth-child(2n) {
                flex-basis: 100%;
            }

            .gallery-item:nth-child(4n) {
                flex-basis: auto;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .gallery-item:nth-child(3n) {
                flex-basis: 100%;
            }

            .gallery-item:nth-child(4n) {
                flex-basis: auto;
            }
        }
    </style>

    <script>
        let lightboxImages = [];
        let currentImageIndex = 0;

        // Adaptive Gallery Layout Logic
        function initializeAdaptiveGallery() {
            const gallery = document.querySelector('.adaptive-gallery');
            const items = gallery.querySelectorAll('.gallery-item');

            if (items.length === 0) return;

            // Get container width
            const containerWidth = gallery.offsetWidth;
            const gap = 8;

            // Calculate optimal layout
            layoutImages(items, containerWidth, gap);
        }

        function layoutImages(items, containerWidth, gap) {
            const targetHeight = getTargetHeight();
            let currentRow = [];
            let currentRowWidth = 0;

            items.forEach((item, index) => {
                const img = item.querySelector('.gallery-image');
                const aspectRatio = getImageAspectRatio(img);
                const itemWidth = targetHeight * aspectRatio;

                // Check if adding this item would exceed container width
                const projectedWidth = currentRowWidth + itemWidth + (currentRow.length * gap);

                if (projectedWidth > containerWidth && currentRow.length > 0) {
                    // Finalize current row
                    finalizeRow(currentRow, containerWidth, gap, targetHeight);

                    // Start new row
                    currentRow = [{
                        item,
                        aspectRatio,
                        width: itemWidth
                    }];
                    currentRowWidth = itemWidth;
                } else {
                    // Add to current row
                    currentRow.push({
                        item,
                        aspectRatio,
                        width: itemWidth
                    });
                    currentRowWidth += itemWidth;
                }

                // If this is the last item, finalize the row
                if (index === items.length - 1) {
                    finalizeRow(currentRow, containerWidth, gap, targetHeight);
                }
            });
        }

        function finalizeRow(row, containerWidth, gap, targetHeight) {
            if (row.length === 0) return;

            const totalGaps = (row.length - 1) * gap;
            const availableWidth = containerWidth - totalGaps;
            const totalOriginalWidth = row.reduce((sum, item) => sum + item.width, 0);
            const scale = availableWidth / totalOriginalWidth;

            row.forEach(({
                item,
                aspectRatio
            }) => {
                const scaledHeight = targetHeight * scale;
                const scaledWidth = scaledHeight * aspectRatio;

                item.style.flexBasis = `${scaledWidth}px`;
                item.style.height = `${scaledHeight}px`;

                const img = item.querySelector('.gallery-image');
                img.style.height = `${scaledHeight}px`;
            });
        }

        function getImageAspectRatio(img) {
            // Try to get actual image dimensions
            if (img.naturalWidth && img.naturalHeight) {
                return img.naturalWidth / img.naturalHeight;
            }
            // Fallback to current dimensions
            return img.offsetWidth / img.offsetHeight || 1.5;
        }

        function getTargetHeight() {
            if (window.innerWidth <= 640) return 150;
            if (window.innerWidth <= 1024) return 180;
            return 220;
        }

        // Initialize gallery when images are loaded
        function handleImageLoad() {
            const images = document.querySelectorAll('.gallery-image');
            let loadedCount = 0;

            images.forEach(img => {
                if (img.complete) {
                    loadedCount++;
                } else {
                    img.addEventListener('load', () => {
                        loadedCount++;
                        if (loadedCount === images.length) {
                            setTimeout(initializeAdaptiveGallery, 100);
                        }
                    });
                }
            });

            if (loadedCount === images.length) {
                setTimeout(initializeAdaptiveGallery, 100);
            }
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', handleImageLoad);

        // Re-layout on window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(initializeAdaptiveGallery, 250);
        });

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

            // Preload adjacent images
            preloadAdjacentImages();
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

        // Preload adjacent images for smoother navigation
        function preloadAdjacentImages() {
            const totalImages = lightboxImages.length;
            if (totalImages <= 1) return;

            const nextIndex = (currentImageIndex + 1) % totalImages;
            const prevIndex = (currentImageIndex - 1 + totalImages) % totalImages;

            // Preload next image
            const nextImg = new Image();
            nextImg.src = lightboxImages[nextIndex].url;

            // Preload previous image
            const prevImg = new Image();
            prevImg.src = lightboxImages[prevIndex].url;
        }
    </script>
@endif
