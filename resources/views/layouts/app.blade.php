<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Stack for any additional styles -->
    @stack('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-white dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            <div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row">
                    <!-- Main Content Area -->
                    <div class="w-full lg:w-3/4">
                        @hasSection('content')
                            @yield('content')
                        @else
                            {{ $slot ?? '' }}
                        @endif
                    </div>

                    <!-- Sidebar - Only visible on lg screens and up -->
                    @include('layouts.sidebar')
                </div>
            </div>
            <div class="mt-20"></div>
        </main>
    </div>

    <!-- Stack for any additional scripts -->
    @stack('scripts')
    @push('scripts')
        <!-- Add this to your layouts/app.blade.php file before </body> tag -->
        <script>
            // Set the login URL for guest users
            const loginUrl = "{{ route('login') }}";
        </script>
        {{-- <script src="{{ asset('js/voting.js') }}"></script> --}}
    @endpush
</body>

</html>
