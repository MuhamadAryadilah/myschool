<x-guest-layout>
    <h2 class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-200">Buat password</h2>

    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Buat password baru untuk akun Anda. Setelah tersimpan, Anda bisa langsung masuk dengan email di bawah ini.
    </p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Token reset password -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password baru -->
        <div class="mt-4">
            <x-input-label for="password" value="Password Baru" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Konfirmasi password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi Password" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Simpan Password
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>