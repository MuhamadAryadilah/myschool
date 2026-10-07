@php
    $qris = config('payment.qris_image');
    $hasQris = $qris && file_exists(public_path($qris));

    $langkah = [
        ['Bayar', 'Transfer bank atau scan QRIS, sesuai nominal tagihan.'],
        ['Simpan bukti', 'Tangkapan layar atau foto struk pembayaran.'],
        ['Unggah bukti', 'Di Dashboard, pada tagihan yang baru dibayar.'],
        ['Tunggu verifikasi', 'Admin memeriksa bukti. Hasilnya dikirim lewat email.'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Cara Bayar</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih transfer bank atau QRIS, lalu unggah bukti pembayarannya.</p>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <section>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Langkah pembayaran</h3>
                <ol class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($langkah as $i => [$judul, $isi])
                        <li class="reveal p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm" style="animation-delay: {{ $i * 100 }}ms">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-bold">{{ $i + 1 }}</span>
                            <p class="mt-3 font-semibold text-gray-900 dark:text-gray-100">{{ $judul }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $isi }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Transfer bank</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Pastikan nama penerima sama dengan
                    <strong class="text-gray-800 dark:text-gray-200">{{ config('payment.account_name') }}</strong>
                    sebelum mengirim.
                </p>

                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    @foreach (config('payment.banks') as $bank)
                        <div class="p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 border-t-4 border-t-brand-500 shadow-sm">
                            <span class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full bg-brand-100 text-brand-800 dark:bg-brand-900 dark:text-brand-100">{{ $bank['name'] }}</span>
                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Nomor rekening</p>
                            <p class="text-2xl font-bold tabular-nums tracking-wider text-gray-900 dark:text-gray-100">{{ $bank['number'] }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">a.n. {{ config('payment.account_name') }}</p>
                            <x-copy-button :text="$bank['number']" label="Salin nomor" class="mt-4 w-full" />
                        </div>
                    @endforeach
                </div>
            </section>

            <section>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">QRIS</h3>

                <div class="mt-3 p-5 sm:p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                    @if ($hasQris)
                        <div x-data="{ zoom: false }" class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                            <div class="w-full max-w-xs shrink-0">
                                <button type="button" @click="zoom = true" aria-label="Perbesar kode QRIS"
                                        class="block w-full rounded-xl bg-white p-3 shadow ring-1 ring-gray-200 hover:ring-brand-500">
                                    <img src="{{ asset($qris) }}" alt="Kode QRIS pembayaran" class="w-full">
                                </button>
                                <div class="mt-3 flex gap-2">
                                    <button type="button" @click="zoom = true"
                                            class="flex-1 px-3 py-1.5 text-sm font-medium text-center rounded-md border border-brand-600 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-gray-700">Perbesar</button>
                                    <a href="{{ asset($qris) }}" download
                                       class="flex-1 px-3 py-1.5 text-sm font-medium text-center rounded-md border border-brand-600 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-gray-700">Unduh gambar</a>
                                </div>
                            </div>

                            <div class="text-sm text-gray-700 dark:text-gray-300 space-y-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">Scan dengan aplikasi pembayaran apa saja</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (['m-banking', 'GoPay', 'OVO', 'DANA', 'ShopeePay'] as $app)
                                        <span class="px-2.5 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">{{ $app }}</span>
                                    @endforeach
                                    <span class="px-2.5 py-0.5 text-xs text-gray-500 dark:text-gray-400">dan lainnya</span>
                                </div>
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Buka aplikasi, lalu pilih menu Scan atau QRIS.</li>
                                    <li>Arahkan kamera ke kode QR ini.</li>
                                    <li>Ketik nominal sesuai tagihan.</li>
                                    <li>Cek nama penerima, lalu konfirmasi pembayaran.</li>
                                </ol>
                            </div>

                            <div x-show="zoom" x-cloak @keydown.escape.window="zoom = false" @click="zoom = false"
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                                <img src="{{ asset($qris) }}" alt="Kode QRIS diperbesar"
                                     class="max-h-full max-w-full rounded-xl bg-white p-4" @click.stop>
                            </div>
                        </div>
                    @else
                        <p class="p-4 text-sm text-center bg-gray-100 dark:bg-gray-900 rounded-md text-gray-600 dark:text-gray-400">
                            Kode QRIS belum tersedia. Silakan gunakan transfer bank.
                        </p>
                    @endif
                </div>
            </section>

            <div class="p-4 rounded-lg bg-accent-100 text-accent-600 text-sm">
                <strong>Penting:</strong> QRIS tidak mengunci nominal, jadi ketik sendiri jumlah yang sama persis dengan tagihan.
                Nominal yang sama membuat bukti Anda cepat diverifikasi admin.
            </div>

            <div class="text-center">
                <a href="{{ route('dashboard') }}"
                   class="inline-block px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-medium rounded-md">
                    Buka Dashboard untuk unggah bukti
                </a>
            </div>
        </div>
    </div>
</x-app-layout>