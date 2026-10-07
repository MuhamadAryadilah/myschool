<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }} - MySchool</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative isolate flex min-h-screen items-center justify-center px-4 font-sans antialiased text-gray-800 dark:text-gray-200 bg-gradient-to-br from-brand-50 via-gray-100 to-gray-100 dark:from-gray-900 dark:via-gray-900 dark:to-brand-900/30">
    <x-bg-orbs />

    <div class="reveal w-full max-w-md p-8 text-center bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 border-t-4 border-t-brand-500 shadow-sm">
        <p class="text-6xl font-bold tracking-tight text-brand-600 dark:text-brand-300">{{ $kode }}</p>
        <h1 class="mt-3 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $judul }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $pesan }}</p>

        <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}"
               class="px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-medium rounded-md">Ke beranda</a>
            <button type="button" onclick="history.length > 1 ? history.back() : (location.href = '/')"
                    class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">Kembali</button>
        </div>
    </div>
</body>
</html>