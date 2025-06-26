<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5CLKHVS');
    </script>
    <!-- End Google Tag Manager -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">

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
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5CLKHVS" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

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
        <main class="py-6">
            <div class="max-w-7xl mx-auto px-0">
                <div class="flex flex-col lg:flex-row">
                    <!-- Main Content Area -->
                    <div
                        class="w-full {{ (!isset($showSidebar) || $showSidebar) && $editorPicks->count() > 0 ? 'lg:w-2/3' : '' }}">
                        @hasSection('content')
                            @yield('content')
                        @else
                            {{ $slot ?? '' }}
                        @endif
                    </div>

                    <!-- Sidebar - Only visible on lg screens and up, when showSidebar is true, and when there are editor picks -->
                    @if ((!isset($showSidebar) || $showSidebar) && $editorPicks->count() > 0)
                        @include('layouts.sidebar')
                    @endif
                </div>
            </div>
            <div class="mt-20"></div>
        </main>
    </div>

    {{-- Enhanced Toast Component - Single source of truth --}}
    <x-toast id="app-toast" />

    <!-- Stack for any additional scripts -->
    @stack('scripts')

    {{-- Global JavaScript Configuration --}}
    <script>
        // Global configuration
        window.AppConfig = {
            loginUrl: "{{ route('login') }}",
            csrfToken: "{{ csrf_token() }}",
            locale: "{{ app()->getLocale() }}"
        };

        // Enhanced global error handler for AJAX requests
        window.handleAjaxError = function(xhr, status, error) {
            console.error('AJAX Error:', {
                xhr,
                status,
                error
            });

            let message = 'Terjadi kesalahan. Silakan coba lagi.';

            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    message = Object.values(errors).flat().join(', ');
                }
            } else if (xhr.status === 419) {
                message = 'Sesi telah berakhir. Silakan refresh halaman.';
            } else if (xhr.status === 403) {
                message = 'Anda tidak memiliki izin untuk melakukan aksi ini.';
            } else if (xhr.status === 404) {
                message = 'Data tidak ditemukan.';
            } else if (xhr.status >= 500) {
                message = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
            }

            ToastManager.create(message, 'error', {
                duration: 5000
            });
        };

        // Enhanced global success handler with toast control
        window.handleAjaxSuccess = function(response, defaultMessage = 'Berhasil!') {
            // Check if response explicitly disables toast (for vote actions)
            if (response.showToast === false) {
                return;
            }

            const message = response.message || defaultMessage;
            ToastManager.create(message, 'success', {
                duration: 4000
            });
        };
    </script>

    {{-- Auto-handle Laravel flash messages - Enhanced with ToastManager --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Laravel flash messages with enhanced ToastManager
            @if (session('success'))
                ToastManager.create('{{ session('success') }}', 'success', {
                    duration: 4000,
                    position: 'top-right'
                });
            @endif

            @if (session('error'))
                ToastManager.create('{{ session('error') }}', 'error', {
                    duration: 5000,
                    position: 'top-right'
                });
            @endif

            @if (session('info'))
                ToastManager.create('{{ session('info') }}', 'info', {
                    duration: 4000,
                    position: 'top-right'
                });
            @endif

            @if (session('warning'))
                ToastManager.create('{{ session('warning') }}', 'error', {
                    duration: 4500,
                    position: 'top-right'
                });
            @endif
        });
    </script>

    <!-- Google Analytics (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-3QTMXJZJ2S"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-3QTMXJZJ2S');
    </script>
</body>

</html>
