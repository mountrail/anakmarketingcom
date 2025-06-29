{{-- resources\views\auth\login-form.blade.php --}}
<div>
    <!-- Login Header -->
    <h2 class="text-4xl font-bold text-center text-branding-primary mb-6">
        Login
    </h2>

    <!-- Success Message -->
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Error Message -->
    @if (session('error'))
        <div class="rounded-md bg-red-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Verification Required Notice -->
    @if (session('verification_required'))
        <div id="verification-notice" class="rounded-md bg-yellow-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Email Not Verified
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>You need to verify your email address before logging in.</p>

                        <!-- Resend verification link using AJAX -->
                        <div class="mt-2">
                            <a href="#" id="resend-verification-link"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-branding-primary hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-branding-primary">
                                <span id="resend-button-text">Resend Verification Email</span>
                                <span id="resend-spinner" class="hidden ml-2 animate-spin">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const resendLink = document.getElementById('resend-verification-link');
                const buttonText = document.getElementById('resend-button-text');
                const spinner = document.getElementById('resend-spinner');
                const noticeDiv = document.getElementById('verification-notice');

                if (resendLink) {
                    resendLink.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Show loading state
                        buttonText.textContent = 'Sending...';
                        spinner.classList.remove('hidden');
                        resendLink.classList.add('opacity-75', 'cursor-not-allowed');

                        // Create form data
                        const formData = new FormData();
                        formData.append('email', '{{ session('email') }}');
                        formData.append('_token', '{{ csrf_token() }}');

                        // Send AJAX request
                        fetch('{{ route('verification.guest.send') }}', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                // Create success message
                                const successDiv = document.createElement('div');
                                successDiv.className = 'rounded-md bg-green-50 p-4 mb-6';
                                successDiv.innerHTML = `
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800">Verification link has been sent!</p>
                                    </div>
                                </div>
                            `;

                                // Insert success message before notice div
                                noticeDiv.parentNode.insertBefore(successDiv, noticeDiv);

                                // Reset button state
                                buttonText.textContent = 'Resend Verification Email';
                                spinner.classList.add('hidden');
                                resendLink.classList.remove('opacity-75', 'cursor-not-allowed');
                            })
                            .catch(error => {
                                // Create error message
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'rounded-md bg-red-50 p-4 mb-6';
                                errorDiv.innerHTML = `
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800">Unable to send verification email. Please try again.</p>
                                    </div>
                                </div>
                            `;

                                // Insert error message before notice div
                                noticeDiv.parentNode.insertBefore(errorDiv, noticeDiv);

                                // Reset button state
                                buttonText.textContent = 'Resend Verification Email';
                                spinner.classList.add('hidden');
                                resendLink.classList.remove('opacity-75', 'cursor-not-allowed');

                                console.error('Error sending verification email:', error);
                            });
                    });
                }
            });
        </script>
    @endif


    <!-- Login Options -->
    <div>
        <!-- Google Login -->
        <div class="flex items-center justify-center mb-6">
            <a href="{{ route('auth.google') }}"
                class="w-full bg-blue-500 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z"
                        fill="currentColor" />
                </svg>
                Login with Google
            </a>
        </div>

        <!-- Email Login Section -->
        {{-- <div
            class="w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold py-3 px-4 rounded text-center mb-6">
            Login with Email
        </div> --}}

        <!-- Login form - Traditional POST submission -->
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="login_email" :value="__('Email')" />
                <x-text-input id="login_email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="login_password" :value="__('Password')" />
                <x-text-input id="login_password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="current-password" showPasswordToggle="true" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Forgot Password Link -->
            @if (Route::has('password.request'))
                <div class="mt-2">
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                </div>
            @endif

            <!-- Remember Me -->
            <div class="block my-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox"
                        class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                        name="remember">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end mt-4">
                <button type="submit"
                    class="bg-branding-primary hover:bg-orange-600 text-white font-bold py-2 px-4 w-full rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-branding-primary inline-flex justify-center items-center">
                    {{ __('Log in') }}
                </button>
            </div>
        </form>
    </div>
</div>
