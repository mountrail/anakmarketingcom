{{-- resources/views/components/share-modal.blade.php --}}
@props(['shareUrl', 'shareTitle', 'shareDescription' => '', 'shareImage' => ''])

<button type="button"
    class="share-button flex items-center text-xs py-1 rounded-md text-gray-900 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors duration-200"
    data-share-url="{{ $shareUrl }}" data-share-title="{{ $shareTitle }}"
    data-share-description="{{ $shareDescription }}" data-share-image="{{ $shareImage }}">
    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
        </path>
    </svg>
    Share
</button>
