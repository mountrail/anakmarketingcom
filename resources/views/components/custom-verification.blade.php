<?php
// resources/views/auth/custom-verification.blade.php

/**
 * This is a custom verification page that matches the design in the screenshot.
 * It should be saved as resources/views/auth/custom-verification.blade.php
 */
?>

<x-app-layout>
    <div class="flex flex-col items-center justify-center h-full p-6">
        <h1 class="text-5xl font-bold text-orange-500 mb-8">User Registered!</h1>

        <div class="text-center mb-8">
            <p class="text-2xl font-medium text-gray-700 dark:text-gray-300">
                Verify your email by clicking on the link sent to your email.
            </p>
        </div>

        @if (session('resent'))
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
                        <p class="text-sm font-medium text-green-800">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    {{ __('Resend Verification Email') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
