{{-- resources/views/profile/partials/form-handler.blade.php --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check for Laravel session messages and show toasts
        @if (session('success'))
            toast('{{ session('success') }}', 'success', {
                duration: 5000,
                position: 'top-right'
            });
        @endif

        @if (session('error'))
            toast('{{ session('error') }}', 'error', {
                duration: 6000,
                position: 'top-right'
            });
        @endif

        @if ($errors->any())
            @php
                $errorMessages = $errors->all();
                $firstError = $errorMessages[0];
            @endphp
            toast('{{ $firstError }}', 'error', {
                duration: 6000,
                position: 'top-right'
            });
        @endif
    });
</script>
