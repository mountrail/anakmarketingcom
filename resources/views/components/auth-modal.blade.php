<!-- resources/views/components/auth-modal.blade.php -->
<div x-data="{
    activeTab: '{{ $activeTab ?? 'login' }}',
    showModal: false
}" @keydown.escape.window="showModal = false"
    @open-auth-modal.window="showModal = true; activeTab = $event.detail || '{{ $activeTab ?? 'login' }}'">

    <!-- Modal Background -->
    <div x-cloak x-show="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <!-- Modal Content -->
        <div @click.away="showModal = false" x-show="showModal"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
            class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-full">

            <!-- Tabs Header -->
            <div class="flex text-center">
                <div @click="activeTab = 'register'"
                    :class="{ 'bg-orange-100 dark:bg-orange-900 text-orange-500': activeTab === 'register', 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': activeTab !== 'register' }"
                    class="w-1/2 py-3 px-4 font-medium cursor-pointer transition-colors">
                    Sign Up
                </div>
                <div @click="activeTab = 'login'"
                    :class="{ 'bg-orange-100 dark:bg-orange-900 text-orange-500': activeTab === 'login', 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': activeTab !== 'login' }"
                    class="w-1/2 py-3 px-4 font-medium cursor-pointer transition-colors">
                    Login
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-6">
                <div x-show="activeTab === 'register'">
                    <x-register-form />
                </div>

                <div x-show="activeTab === 'login'">
                    <x-login-form />
                </div>
            </div>
        </div>
    </div>
</div>
