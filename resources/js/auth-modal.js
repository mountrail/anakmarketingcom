// resources/js/auth-modal.js
document.addEventListener('DOMContentLoaded', function () {
    // Handle all login buttons
    document.querySelectorAll('[data-auth-action="login"]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
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
