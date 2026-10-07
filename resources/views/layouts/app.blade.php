@php
    $routeName = request()->route()?->getName();
    $backUrl = null;
    $backLabel = null;

    if ($routeName) {
        if (str_starts_with($routeName, 'admin.registration-steps.')) {
            $backUrl = route('admin.registration-settings.edit');
            $backLabel = 'Kembali ke Aturan Pendaftaran';
        } elseif ($routeName === 'admin.registration-settings.edit') {
            $backUrl = route('admin.dashboard');
            $backLabel = 'Kembali ke dashboard';
        } elseif (preg_match('/^admin\.(\w+)(?:\.bulk)?\.(create|edit|show)$/', $routeName, $m)) {
            $backUrl = route('admin.'.$m[1].'.index');
            $backLabel = 'Kembali ke daftar';
        } elseif (preg_match('/^admin\.\w+\.index$/', $routeName) || in_array($routeName, ['payments.history', 'payments.methods', 'profile.edit'], true)) {
            $backUrl = auth()->user()?->isAdmin() ? route('admin.dashboard') : route('dashboard');
            $backLabel = 'Kembali ke dashboard';
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" style="color-scheme: dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ (isset($header) && preg_match('/<h2[^>]*>(.*?)<\/h2>/s', (string) $header, $mt) ? trim(preg_replace('/\s+/', ' ', strip_tags($mt[1]))).' - ' : '').config('app.name', 'MySchool') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @include('partials.demo-bar')
        <div class="relative isolate min-h-screen bg-gradient-to-br from-brand-50 via-sky-50 to-gray-100 dark:from-gray-900 dark:via-gray-900 dark:to-brand-900/30"><x-bg-orbs />
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        @if ($backUrl)
                            <a href="{{ $backUrl }}"
                               class="mb-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-brand-600 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-gray-700 transition">
                                &larr; {{ request()->routeIs('admin.invoices.bulk.*') ? 'Kembali' : $backLabel }}
                            </a>
                        @endif
                        {{ $header }}
                    </div>
                </header>
            @elseif ($backUrl)
                <div class="max-w-7xl mx-auto pt-6 px-4 sm:px-6 lg:px-8">
                    <a href="{{ $backUrl }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-brand-600 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-gray-700 transition">
                        &larr; {{ request()->routeIs('admin.invoices.bulk.*') ? 'Kembali' : $backLabel }}
                    </a>
                </div>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @include('partials.demo-tour')
</body>
</html>