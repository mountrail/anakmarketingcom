<!-- resources/views/components/register-form.blade.php -->
<div x-data="{
    showRegisterForm: false,
    registrationStep: 1,
    registrationSuccess: false,
    isSubmitting: false,

    // Method to refresh CSRF token
    refreshCsrfToken() {
        return fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
        .then(() => {
            // Get the refreshed token from meta tag
            const refreshedToken = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content');
            // Update the form's hidden input
            document.getElementById('register_csrf_token').value = refreshedToken;
            console.log('Registration CSRF token refreshed');
            return refreshedToken;
        })
        .catch(error => {
            console.error('Error refreshing CSRF token:', error);
        });
    }
}">
    <!-- Registration Success Message -->
    <div x-show="registrationSuccess" class="text-center">
        <h2 class="text-4xl font-bold text-center text-orange-500 mb-6">User Registered!</h2>
        <p class="text-lg mb-8">Verify your email by clicking on the link sent to your email.</p>
        <button @click="showModal = false; registrationStep = 1; registrationSuccess = false;"
            class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded">
            Close
        </button>
    </div>

    <!-- Register Form Content (Hidden when success is shown) -->
    <div x-show="!registrationSuccess">
        <!-- Register Headers -->
        <h2 class="text-4xl font-bold text-center text-orange-500 mb-6" x-show="registrationStep === 1">
            Sign Up
        </h2>
        <h2 class="text-4xl font-bold text-center text-orange-500 mb-6" x-show="registrationStep === 2">
            One Last Step
        </h2>

        <!-- Register Content -->
        <div>
            <div class="flex items-center justify-center mb-6" x-show="registrationStep === 1">
                <a href="{{ route('auth.google') }}"
                    class="w-full bg-blue-500 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z"
                            fill="currentColor" />
                    </svg>
                    Continue with Google
                </a>
            </div>

            <div @click="showRegisterForm = !showRegisterForm; if(showRegisterForm) { refreshCsrfToken(); }" x-show="registrationStep === 1"
                class="w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold py-3 px-4 rounded text-center cursor-pointer mb-6">
                Register with Email
            </div>

            <div x-show="showRegisterForm || registrationStep === 2" class="mt-4">
                <!-- Registration form with step control -->
                <x-register-form-steps />
            </div>
        </div>
    </div>
</div>
