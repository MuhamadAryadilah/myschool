@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

    $cards = [
        ['label' => 'Menunggu verifikasi', 'value' => $stats['pending'], 'href' => route('admin.payments.index'),
         'bar' => 'bg-yellow-400', 'chip' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400',
         'text' => $stats['pending'] > 0 ? 'text-yellow-500' : 'text-gray-900 dark:text-gray-100',
         'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Tagihan terlambat', 'value' => $stats['overdue'], 'href' => route('admin.invoices.index', ['status' => 'terlambat']),
         'bar' => 'bg-red-400', 'chip' => 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400',
         'text' => $stats['overdue'] > 0 ? 'text-red-500' : 'text-gray-900 dark:text-gray-100',
         'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
        ['label' => 'Belum lunas', 'value' => $rp($stats['unpaid']), 'href' => route('admin.invoices.index', ['status' => 'belum_lunas']),
         'bar' => 'bg-gray-400', 'chip' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
         'text' => 'text-gray-900 dark:text-gray-100',
         'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z'],
        ['label' => 'Sudah terkumpul', 'value' => $rp($stats['collected']), 'href' => route('admin.invoices.index', ['status' => 'lunas']),
         'bar' => 'bg-green-400', 'chip' => 'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400',
         'text' => 'text-green-500',
         'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];

    $menus = [
        ['route' => 'admin.payments.index', 'title' => 'Pembayaran', 'desc' => 'Cek bukti bayar yang masuk dari orang tua', 'badge' => $stats['pending'],
         'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
        ['route' => 'admin.invoices.index', 'title' => 'Tagihan', 'desc' => 'Daftar tagihan, rekap semester, dan tagihan massal',
         'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
        ['route' => 'admin.students.index', 'title' => 'Siswa', 'desc' => 'Kelola data siswa',
         'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
        ['route' => 'admin.parents.index', 'title' => 'Orang Tua', 'desc' => 'Kelola akun orang tua',
         'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['route' => 'admin.registrations.index', 'title' => 'Pendaftaran', 'desc' => 'Lihat dan proses pendaftar baru', 'badge' => $stats['registrations'],
         'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z'],
        ['route' => 'admin.registration-settings.edit', 'title' => 'Aturan Pendaftaran', 'desc' => 'Batas usia dan alur setelah daftar',
         'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
        ['route' => 'admin.extracurriculars.index', 'title' => 'Eskul', 'desc' => 'Kelola ekstrakurikuler dan anggotanya',
         'icon' => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'],
        ['route' => 'admin.news.index', 'title' => 'Berita', 'desc' => 'Kelola berita sekolah',
         'icon' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            MySchool Admin
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Banner sapaan --}}
            <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 px-5 py-6 sm:px-8 sm:py-8 text-white shadow-sm">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
                <div class="absolute -right-2 top-16 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="relative">
                    <p class="text-sm text-white/80">{{ now()->translatedFormat('l, j F Y') }}</p>
                    <h3 class="mt-1 text-xl sm:text-2xl font-bold break-words">Halo, {{ auth()->user()->name }}</h3>
                    <p class="mt-1 text-sm text-white/80">Ringkasan tagihan dan pembayaran.</p>
                </div>
            </div>

            {{-- Rekap --}}
            @php
                $tints = [
                    'bg-yellow-50 ring-yellow-300 dark:bg-yellow-500/10 dark:ring-yellow-500/40',
                    'bg-red-50 ring-red-300 dark:bg-red-500/10 dark:ring-red-500/40',
                    'bg-gray-100 ring-gray-300 dark:bg-gray-700/50 dark:ring-gray-500',
                    'bg-green-50 ring-green-300 dark:bg-green-500/10 dark:ring-green-500/40',
                ];
            @endphp
            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Ringkasan tagihan</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    @foreach ($cards as $card)
                        <a href="{{ $card['href'] }}" class="group relative block overflow-hidden p-4 sm:p-5 rounded-lg ring-1 shadow-md hover:shadow-lg transition {{ $tints[$loop->index] }}">
                            <span class="absolute inset-y-0 left-0 w-1.5 {{ $card['bar'] }}"></span>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $card['chip'] }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                                </span>
                                <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300">{{ $card['label'] }}</p>
                            </div>
                            <p class="mt-3 text-lg sm:text-2xl font-bold tabular-nums break-words {{ $card['text'] }}">{{ $card['value'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Menu --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach ($menus as $menu)
                    <a href="{{ route($menu['route']) }}"
                       class="group flex items-center gap-3 p-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-transparent hover:border-brand-500/40 hover:shadow-md transition">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $menu['icon'] }}"/></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-x-2 gap-y-1 font-semibold text-gray-900 dark:text-gray-100">
                                {{ $menu['title'] }}
                                @if (($menu['badge'] ?? 0) > 0)
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-300">{{ $menu['badge'] }} menunggu</span>
                                @endif
                            </span>
                            <span class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">{{ $menu['desc'] }}</span>
                        </span>
                        <svg class="h-5 w-5 shrink-0 text-gray-300 dark:text-gray-600 transition group-hover:translate-x-0.5 group-hover:text-brand-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>