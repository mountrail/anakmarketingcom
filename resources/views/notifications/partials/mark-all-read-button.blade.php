{{-- resources/views/notifications/partials/mark-all-read-button.blade.php --}}
<div class="text-center mb-6">
    <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
        @csrf
        @method('PATCH')
        <x-primary-button type="submit" variant="primary" size="md">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Tandai Semua Sudah Dibaca
        </x-primary-button>
    </form>
</div>
