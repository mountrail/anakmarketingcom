{{-- resources\views\auth\register-form-steps.blade.php --}}
<form method="POST" action="{{ route('register') }}" id="register-form"
    @submit.prevent="
    if (registrationStep === 1) {
        isSubmitting = true;

        // Use the global CSRF token refreshing function
        window.refreshAuthCsrfToken()
            .then(csrfToken => {
                // Gather form data
                const formData = {
                    name: document.getElementById('register_name').value,
                    email: document.getElementById('register_email').value,
                    phone_country_code: document.getElementById('register_phone_country_code').value,
                    phone_number: document.getElementById('register_phone_number').value,
                    password: document.getElementById('register_password').value,
                    password_confirmation: document.getElementById('register_password_confirmation').value
                };

                // Clear any existing errors
                document.querySelectorAll('.text-red-500').forEach(el => {
                    el.textContent = '';
                    el.classList.add('hidden');
                });

                // Send validation request for step 1
                return fetch('{{ route('register.validate.step1') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => { throw data; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Move to step 2 on successful validation
                    registrationStep = 2;
                }
            })
            .catch(error => {
                // Handle validation errors
                if (error.errors) {
                    Object.keys(error.errors).forEach(field => {
                        const errorEl = document.getElementById(field + '-error');
                        if (errorEl) {
                            errorEl.textContent = error.errors[field][0];
                            errorEl.classList.remove('hidden');
                        }

                        // Special handling for email field which might be named differently
                        if (field === 'email') {
                            const registerEmailError = document.getElementById('register_email-error');
                            if (registerEmailError) {
                                registerEmailError.textContent = error.errors[field][0];
                                registerEmailError.classList.remove('hidden');
                            }
                        }
                    });
                } else {
                    console.error('Validation error:', error);
                }
            })
            .finally(() => {
                isSubmitting = false;
            });
    } else {
        // Handle step 2 submission (final registration)
        isSubmitting = true;

        // Use the global CSRF token refreshing function first
        window.refreshAuthCsrfToken()
            .then(csrfToken => {
                const formData = new FormData(document.getElementById('register-form'));

                // Submit the registration form
                return fetch('{{ route('register') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => { throw data; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    registrationSuccess = true;
                }
            })
            .catch(error => {
                // Handle registration errors
                if (error.errors) {
                    // Clear previous errors first
                    document.querySelectorAll('.text-red-500').forEach(el => {
                        el.textContent = '';
                        el.classList.add('hidden');
                    });

                    // Display new errors
                    Object.keys(error.errors).forEach(field => {
                        const errorEl = document.getElementById(field + '-error');
                        if (errorEl) {
                            errorEl.textContent = error.errors[field][0];
                            errorEl.classList.remove('hidden');
                        }
                    });
                } else {
                    console.error('Registration error:', error);
                }
            })
            .finally(() => {
                isSubmitting = false;
            });
    }">
    @csrf
    <!-- Dynamic CSRF token input that can be updated if needed -->
    <input type="hidden" id="register_csrf_token" name="_token" value="{{ csrf_token() }}">

    <!-- Step 1: Personal Information -->
    <div x-show="registrationStep === 1">
        @include('auth.register-personal-info')
    </div>

    <!-- Step 2: Professional Information -->
    <div x-show="registrationStep === 2">
        @include('auth.register-professional-info')

        <!-- Back Button - Return to Step 1 -->
        <div class="flex items-center justify-start mt-4">
            <button type="button" @click="registrationStep = 1"
                class="text-sm text-branding-primary hover:text-orange-600 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to personal details
            </button>
        </div>
    </div>

    <!-- Form Footer -->
    <div class="flex items-center justify-end mt-4">

        <button type="submit"
            class="w-full bg-branding-primary hover:bg-orange-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-branding-primary inline-flex items-center justify-center"
            :disabled="isSubmitting">
            <span x-show="isSubmitting" class="inline-block animate-spin mr-2">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>
            <span x-text="registrationStep === 1 ? 'Continue' : 'Register'">{{ __('Continue') }}</span>
        </button>
    </div>
</form>
