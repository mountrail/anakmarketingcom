{{-- resources\views\auth\forgot-password.blade.php --}}
<x-app-layout>
    <div class="mt-24 px-6">
        @if (session('status'))
            {{-- Success state - show message and back button --}}
            <div class="text-center">
                <div class="mb-6 text-lg font-semibold text-essentials-success">
                    Kami telah mengirimkan tautan reset kata sandi ke email Anda
                </div>

                <div class="mb-4 text-sm text-branding-dark">
                    Silakan periksa kotak masuk email Anda dan klik tautan untuk mereset kata sandi.
                </div>

                <button onclick="window.location.reload()"
                    class="inline-flex items-center px-4 py-2 bg-branding-dark border border-transparent rounded-md font-semibold text-xs text-branding-light uppercase tracking-widest hover:bg-branding-black focus:bg-branding-black active:bg-branding-black focus:outline-none focus:ring-2 focus:ring-branding-primary focus:ring-offset-2 transition ease-in-out duration-150">
                    ← Kembali
                </button>
            </div>
        @else
            {{-- Initial state - show form --}}
            <div class="mb-4 text-sm text-branding-dark">
                Lupa kata sandi? Tidak masalah. Cukup beri tahu kami alamat email Anda dan kami akan mengirimkan tautan
                reset kata sandi yang memungkinkan Anda untuk memilih yang baru.
            </div>

            <!-- Display any errors -->
            @if ($errors->any())
                <div class="mb-4 text-sm text-essentials-alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="'Email'" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                        :value="old('email')" required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-primary-button type="submit">
                        Kirim Tautan Reset Kata Sandi
                    </x-primary-button>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
