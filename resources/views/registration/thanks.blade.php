<x-public-layout title="Pendaftaran Terkirim">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Pendaftaran terkirim</h1>

        @if ($ref !== '-')
            <p class="mt-4 text-gray-600 dark:text-gray-300">
                Nomor pendaftaran Anda: <strong class="text-gray-900 dark:text-gray-100">{{ $ref }}</strong>
            </p>
        @endif

        <p class="mt-4 text-gray-600 dark:text-gray-300">
            Sekolah akan memeriksa data yang Anda kirim. Hasilnya diberitahukan ke email yang Anda daftarkan.
            Jika diterima, Anda akan menerima email berisi link untuk membuat password akun MySchool.
        </p>

        @include('registration._steps')

        <a href="{{ route('home') }}"
           class="mt-8 inline-block px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-medium rounded-md">
            Kembali ke beranda
        </a>
    </div>
</x-public-layout>