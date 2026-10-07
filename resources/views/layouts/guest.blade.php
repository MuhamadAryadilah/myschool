<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" style="color-scheme: dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MySchool') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @include('partials.demo-bar')
        <div class="relative isolate min-h-screen lg:grid lg:grid-cols-2 bg-gradient-to-br from-brand-50 via-gray-100 to-gray-100 dark:from-gray-900 dark:via-gray-900 dark:to-brand-900/30">
            <x-bg-orbs />

            {{-- Panel kiri: hanya tampil di laptop --}}
            <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-brand-700 to-brand-900 p-12 text-white">
                <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/10"></div>
                <div class="absolute -left-16 bottom-10 h-56 w-56 rounded-full bg-white/10"></div>

                <a href="/" class="relative text-2xl font-bold tracking-tight">MySchool</a>

                <div class="relative">
                    <h2 class="text-4xl font-bold tracking-tight">Portal tagihan dan pembayaran sekolah</h2>
                    <ul class="mt-8 space-y-4 text-brand-100">
                        @foreach (['Lihat tagihan anak kapan saja', 'Unggah bukti bayar langsung dari HP', 'Kuitansi bisa diunduh setelah lunas'] as $poin)
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                {{ $poin }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="relative text-sm text-brand-100/80">&copy; {{ date('Y') }} MySchool</p>
            </aside>

            {{-- Kolom form --}}
            <div class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0">
                <div class="text-center lg:hidden">
                    <a href="/" class="text-3xl font-bold tracking-tight text-brand-700 dark:text-brand-300">
                        MySchool
                    </a>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Portal tagihan dan pembayaran sekolah</p>
                </div>

                <div class="reveal w-full sm:max-w-md mt-6 lg:mt-0 px-6 py-6 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden sm:rounded-lg">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @include('partials.demo-tour')
</body>
</html>