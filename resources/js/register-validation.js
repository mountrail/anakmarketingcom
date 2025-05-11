// resources/js/register-validation.js
document.addEventListener('DOMContentLoaded', function () {
    // Find the register form
    const registerForm = document.getElementById('register-form');

    if (registerForm) {
        // Set up Alpine.js data - this integrates with your existing Alpine structure
        if (window.Alpine) {
            // Find the parent Alpine component
            const alpineRoot = registerForm.closest('[x-data]');
            if (alpineRoot) {
                // Extend Alpine data with our step properties
                const alpineComponent = Alpine.$data(alpineRoot);
                if (!alpineComponent.hasOwnProperty('registrationStep')) {
                    alpineComponent.registrationStep = 1;
                }
            }
        }

        // Clear all previous error messages
        const clearErrors = () => {
            registerForm.querySelectorAll('.text-red-500').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });
        };

        // Validation submission handler
        registerForm.addEventListener('submit', function (e) {
            // Only intercept if this is step 1
            if (this.hasAttribute('data-validate-step1')) {
                e.preventDefault();
                clearErrors();

                // Get form data
                const formData = new FormData(this);

                // Send AJAX request to validate first step
                fetch('/register/validate-step1', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Find the Alpine component
                        const alpineRoot = registerForm.closest('[x-data]');
                        if (alpineRoot) {
                            const alpineComponent = Alpine.$data(alpineRoot);
                            // Update Alpine registration step
                            alpineComponent.registrationStep = 2;

                            // Remove validation attribute as we're now on step 2
                            registerForm.removeAttribute('data-validate-step1');

                            // Update submit button text
                            const submitButton = registerForm.querySelector('button[type="submit"]');
                            if (submitButton) {
                                submitButton.textContent = 'Sign Up';
                            }

                            // Update the heading if it exists
                            const heading = document.querySelector('h2[x-show="activeTab === \'register\'"]');
                            if (heading) {
                                heading.textContent = 'One Last Step';
                            }
                        }
                    } else {
                        // Display validation errors
                        Object.keys(data.errors).forEach(field => {
                            const errorElement = document.getElementById(`${field}-error`);
                            if (errorElement) {
                                errorElement.textContent = data.errors[field][0];
                                errorElement.classList.remove('hidden');
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    }
});
