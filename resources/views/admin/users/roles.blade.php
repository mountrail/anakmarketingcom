<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit User Roles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <p class="text-lg font-medium">{{ $user->name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                    </div>

                    <form method="POST" action="{{ route('admin.users.roles.update', $user->id) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Roles
                            </label>

                            <div class="space-y-2">
                                @foreach ($roles as $role)
                                    <div class="flex items-center">
                                        <input id="role-{{ $role->id }}" name="roles[]" type="checkbox"
                                            value="{{ $role->id }}"
                                            {{ $user->hasRole($role->name) ? 'checked' : '' }}
                                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        <label for="role-{{ $role->id }}"
                                            class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                            {{ ucfirst($role->name) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md mr-2">
                                Cancel
                            </a>
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
