{{-- resources/views/components/navigation/mobile-auth-bar.blade.php --}}
<div
    class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 py-2 sm:hidden z-30">
    <div class="flex justify-between items-center space-x-4">
        {{-- Sign Up Button (40%) --}}
        <x-primary-button variant="dark" size="md" class="w-2/5 font-bold"
            @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'register'}))">
            Daftar
        </x-primary-button>

        {{-- Login Button (60%) --}}
        <x-primary-button size="md" class="w-3/5 font-bold"
            @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'login'}))">
            Masuk
        </x-primary-button>
    </div>
</div>
