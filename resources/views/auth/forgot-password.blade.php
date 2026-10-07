<x-guest-layout>
    <h2 class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-200">Lupa password</h2>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Masukkan email yang Anda daftarkan. Kami akan mengirim tautan baru untuk membuat password. Gunakan ini juga jika tautan dari email penerimaan sudah kedaluwarsa.
    </div>

    <!-- Status sesi -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Kirim Tautan Baru
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>