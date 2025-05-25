{{-- resources/views/components/navigation/scripts.blade.php --}}
<script>
    // Initialize event handlers for auth modal triggers
    document.addEventListener('DOMContentLoaded', function() {
        // Handle data-auth-action attributes
        document.querySelectorAll('[data-auth-action]').forEach(function(element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.getAttribute('data-auth-action');
                window.dispatchEvent(new CustomEvent('open-auth-modal', {
                    detail: action
                }));
            });
        });
    });
</script>
