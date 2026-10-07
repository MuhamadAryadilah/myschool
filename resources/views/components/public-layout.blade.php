@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" style="color-scheme: dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' - ' : '' }}MySchool</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="relative isolate min-h-screen font-sans antialiased bg-gradient-to-br from-brand-50 via-gray-100 to-gray-100 dark:from-gray-900 dark:via-gray-900 dark:to-brand-900/30 text-gray-800 dark:text-gray-200"><x-bg-orbs />
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="shrink-0 text-lg font-bold tracking-tight text-brand-700 dark:text-brand-300">
                    MySchool
                </a>

                <nav class="flex shrink-0 items-center gap-2 text-sm font-medium sm:gap-5">
                    <a href="{{ route('news.index') }}"
                       class="whitespace-nowrap text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-300">Berita</a>

                    <a href="{{ route('registration.create') }}"
                       class="whitespace-nowrap text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-300"><span class="hidden sm:inline">Daftar Siswa Baru</span><span class="sm:hidden">Daftar</span></a>

                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="whitespace-nowrap rounded-md bg-brand-600 px-3 py-2 text-white hover:bg-brand-500 sm:px-4">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}"
                           class="whitespace-nowrap rounded-md bg-brand-600 px-3 py-2 text-white hover:bg-brand-500 sm:px-4">Masuk</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>
        <x-site-footer />
    @include('partials.demo-tour')
</body>
</html>