<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Home') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-4">Welcome to Anak Marketing!</h1>
                    <p class="mb-4">This is our public homepage that anyone can access, whether they're logged in or not.</p>

                    @guest
                        <div class="mt-6">
                            <p class="mb-4">To access additional features, please log in or create an account:</p>
                            <div class="flex space-x-4">
                                <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded">
                                    Log In
                                </a>
                                <a href="{{ route('register') }}" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded">
                                    Register
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mt-6">
                            <p>You're logged in as <strong>{{ Auth::user()->name }}</strong>!</p>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
