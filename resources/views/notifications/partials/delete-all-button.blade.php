{{-- resources/views/notifications/partials/delete-all-button.blade.php --}}
<div class="text-center mb-6">
    <form action="{{ route('notifications.delete-all') }}" method="POST" class="inline"
        onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua notifikasi? Notifikasi sistem tidak akan dihapus. Tindakan ini tidak dapat dibatalkan.')">
        @csrf
        @method('DELETE')
        <x-primary-button type="submit" variant="secondary" size="md"
            class="border-red-300 text-red-600 hover:bg-red-50 dark:border-red-600 dark:text-red-400 dark:hover:bg-red-900/20">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                </path>
            </svg>
            Hapus Semua Notifikasi
        </x-primary-button>
    </form>
</div>
