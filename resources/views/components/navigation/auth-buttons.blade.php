{{-- resources/views/components/navigation/auth-buttons.blade.php --}}
<div class="flex items-center space-x-4">
    <x-primary-button variant="dark" size="md"
        @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'register'}))">
        Sign Up
    </x-primary-button>
    <x-primary-button size="md"
        @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', {detail: 'login'}))">
        Login
    </x-primary-button>
</div>
