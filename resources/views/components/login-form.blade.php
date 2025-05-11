<!-- resources/views/components/login-form.blade.php -->
<div x-data="{
    showLoginForm: false,
    isSubmitting: false
}">
    <!-- Login Header -->
    <h2 class="text-4xl font-bold text-center text-orange-500 mb-6">
        Login
    </h2>

    <!-- Login Content -->
    <div>
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

        <div @click="showLoginForm = !showLoginForm"
            class="w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold py-3 px-4 rounded text-center cursor-pointer mb-6">
            Login with Email
        </div>

        <div x-show="showLoginForm" class="mt-4">
            <!-- Login form -->
            <form method="POST" action="{{ route('login') }}" id="login-form"
                @submit.prevent="
                isSubmitting = true;
                document.getElementById('login-form').submit();">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="login_email" :value="__('Email')" />
                    <x-text-input id="login_email" class="block mt-1 w-full" type="email" name="email"
                        :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="login_password" :value="__('Password')" />

                    <x-text-input id="login_password" class="block mt-1 w-full" type="password" name="password" required
                        autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox"
                            class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                            name="remember">
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                            href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <button type="submit"
                        class="ms-3 bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 inline-flex items-center"
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
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
