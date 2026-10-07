<x-guest-layout>
    <h2 class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-200">Konfirmasi password</h2>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Ini adalah area yang dilindungi. Mohon konfirmasi password Anda sebelum melanjutkan.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                Konfirmasi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>