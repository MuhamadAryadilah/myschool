<x-public-layout title="Beranda">
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 to-brand-900 text-white">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10"></div>
        <div class="absolute right-24 bottom-[-4rem] h-40 w-40 rounded-full bg-white/10"></div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20">
            <h1 class="reveal text-3xl sm:text-5xl font-bold tracking-tight">Kabar terbaru dari MySchool</h1>
            <p class="reveal mt-4 max-w-2xl text-brand-100" style="animation-delay: 100ms">
                Berita sekolah dan pendaftaran siswa baru secara online. Orang tua siswa bisa masuk untuk melihat tagihan dan riwayat pembayaran.
            </p>
            <div class="reveal mt-8 flex flex-col items-start gap-3" style="animation-delay: 200ms">
                <a href="{{ route('registration.create') }}"
                   class="inline-block px-6 py-3 text-center bg-white text-brand-800 font-medium rounded-md shadow-sm hover:bg-brand-50 active:scale-[0.97] transition">Daftar Siswa Baru</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-white underline underline-offset-4 hover:no-underline">Buka dashboard &rarr;</a>
                @else
                    <p class="text-sm text-brand-100">Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-semibold text-white underline underline-offset-4 hover:no-underline">Masuk</a>
                    </p>
                @endauth
            </div>
        </div>
    </section>

    {{-- Langkah pendaftaran --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Langkah pendaftaran</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['Isi formulir', 'Isi data calon siswa dan orang tua secara online, dari HP pun bisa.'],
                ['Sekolah memeriksa', 'Pihak sekolah memeriksa data Anda. Hasilnya dikirim ke email.'],
                ['Buat akun', 'Jika diterima, Anda menerima link untuk membuat password akun MySchool.'],
            ] as [$judul, $isi])
                <div class="reveal flex items-start gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm"
                     style="animation-delay: {{ 100 + $loop->index * 100 }}ms">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white font-bold">{{ $loop->iteration }}</span>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $judul }}</p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $isi }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        @if ($featured)
            <a href="{{ route('news.show', $featured) }}"
               class="group reveal block p-5 sm:p-8 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 border-l-4 border-l-brand-500 shadow-sm hover:shadow-md transition">
                @if ($featured->images->isNotEmpty())
                    <div class="mb-4 h-48 sm:h-64 overflow-hidden rounded-md">
                        <img src="{{ $featured->images->first()->url() }}" alt=""
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    </div>
                @endif
                <span class="inline-block px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded-full bg-accent-100 text-accent-600">Terbaru</span>
                <h2 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 break-words">{{ $featured->title }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $featured->created_at->translatedFormat('j F Y') }}@if ($featured->author) &middot; {{ $featured->author }}@endif
                </p>
                <p class="mt-3 text-gray-600 dark:text-gray-300 break-words">{{ $featured->excerpt(260) }}</p>
                <span class="mt-4 inline-block text-sm font-medium text-brand-600 dark:text-brand-300">Baca selengkapnya &rarr;</span>
            </a>

            @if ($others->isNotEmpty())
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($others as $item)
                        @include('news._card', ['item' => $item, 'delay' => $loop->index * 80])
                    @endforeach
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('news.index') }}"
                       class="inline-block px-5 py-2 border border-brand-600 text-brand-700 dark:text-brand-300 rounded-md hover:bg-brand-50 dark:hover:bg-gray-800 transition">
                        Lihat semua berita
                    </a>
                </div>
            @endif
        @else
            <div class="p-10 text-center bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <p class="text-gray-600 dark:text-gray-300">Belum ada berita.</p>
            </div>
        @endif
    </section>
</x-public-layout>