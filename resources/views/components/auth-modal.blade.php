{{-- resources/views/components/auth-modal.blade.php --}}
<div x-data="{
    activeTab: '{{ $activeTab ?? 'login' }}',
    showModal: {{ session('show_auth_modal') ? 'true' : 'false' }}
}" @keydown.escape.window="showModal = false"
    @open-auth-modal.window="showModal = true; activeTab = $event.detail || '{{ $activeTab ?? 'login' }}'">

    <!-- Modal Background -->
    <div x-cloak x-show="showModal"
        class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black bg-opacity-50 p-4 pt-8"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <!-- Modal Content -->
        <div @click.away="showModal = false" x-show="showModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-full mx-auto mt-4 mb-16">

            <!-- Close Button -->
            <div class="absolute top-0 right-0 p-4">
                <button @click="showModal = false"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Tabs Header -->
            <div class="flex text-center sticky top-0 z-10">
                <div @click="activeTab = 'register'"
                    :class="{ 'bg-branding-light dark:bg-gray-900 ': activeTab === 'register', 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300': activeTab !== 'register' }"
                    class="w-1/2 py-3 px-4 font-bold cursor-pointer transition-colors">
                    Sign Up
                </div>
                <div @click="activeTab = 'login'"
                    :class="{ 'bg-branding-light dark:bg-gray-900 ': activeTab === 'login', 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300': activeTab !== 'login' }"
                    class="w-1/2 py-3 px-4 font-bold cursor-pointer transition-colors">
                    Login
                </div>
            </div>

            <!-- Content Area -->
            <div class="py-6 px-8 max-h-[80vh] overflow-y-auto">
                <div x-show="activeTab === 'register'">
                    {{-- Pass empty errors collection to avoid undefined variable error --}}
                    @php
                        $errors = $errors ?? new \Illuminate\Support\MessageBag();
                    @endphp
                    @include('auth.register-form')
                </div>

                <div x-show="activeTab === 'login'">
                    {{-- Pass empty errors collection to avoid undefined variable error --}}
                    @php
                        $errors = $errors ?? new \Illuminate\Support\MessageBag();
                    @endphp
                    @include('auth.login-form')
                </div>
            </div>
        </div>
    </div>
</div>

@if (session('show_auth_modal'))
    <script>
        // Force the correct tab if specified
        document.addEventListener('DOMContentLoaded', function() {
            window.dispatchEvent(new CustomEvent('open-auth-modal', {
                detail: '{{ session('show_auth_modal') }}'
            }));
        });
    </script>
@endif
