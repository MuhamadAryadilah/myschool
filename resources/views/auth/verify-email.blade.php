<x-guest-layout>
    <h2 class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-200">Verifikasi email</h2>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Terima kasih sudah mendaftar. Sebelum mulai, mohon verifikasi alamat email Anda dengan mengklik tautan yang baru kami kirim. Kalau email belum masuk, kami dengan senang hati mengirim ulang.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-brand-600 dark:text-brand-300">
            Tautan verifikasi baru sudah dikirim ke alamat email yang Anda gunakan saat mendaftar.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Kirim Ulang Email Verifikasi
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 dark:focus:ring-offset-gray-800">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>