// resources/js/auth-modal.js
document.addEventListener('DOMContentLoaded', function () {

    // Global CSRF token refresh function
    window.refreshAuthCsrfToken = function () {
        return fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(() => {
                // Get the refreshed token from meta tag
                const refreshedToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Update all dynamic CSRF token inputs in the page
                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = refreshedToken;
                });

                // Also update any dynamic_csrf_token elements specifically
                document.querySelectorAll('#dynamic_csrf_token').forEach(input => {
                    input.value = refreshedToken;
                });

                console.log('CSRF token refreshed globally');
                return refreshedToken;
            })
            .catch(error => {
                console.error('Error refreshing CSRF token:', error);
                throw error; // Re-throw to allow handling in the calling code
            });
    };

    // Handle all login buttons
    document.querySelectorAll('[data-auth-action="login"]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            // Store intended URL in Laravel session
            fetch('/store-intended-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ intended_url: window.location.href })
            });
            window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: 'login' }));
        });
    });

    // Handle all register buttons
    document.querySelectorAll('[data-auth-action="register"]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: 'register' }));
        });
    });

    // Handle Google login links
    document.addEventListener('click', function (e) {
        if (e.target.closest('a[href*="auth/google"]')) {
            // Store intended URL before Google redirect
            fetch('/store-intended-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ intended_url: window.location.href })
            });
        }
    });

    // Initialize Alpine.js reactivity for registration steps if it's not already available
    if (window.Alpine) {
        // This ensures Alpine properties are properly set up for new instances
        document.addEventListener('alpine:initialized', () => {
            const authModals = document.querySelectorAll('[x-data*="activeTab"]');
            authModals.forEach(modal => {
                const alpineData = Alpine.$data(modal);
                if (!alpineData.hasOwnProperty('registrationStep')) {
                    alpineData.registrationStep = 1;
                }
            });
        });
    }
});
