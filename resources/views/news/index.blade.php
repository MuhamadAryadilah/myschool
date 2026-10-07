<x-public-layout title="Berita">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        @auth
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
               class="mb-4 inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-brand-600 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-gray-700 transition">&larr; Kembali ke dashboard</a>
        @else
            <a href="{{ route('home') }}"
               class="mb-4 inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-brand-600 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-gray-700 transition">&larr; Kembali ke beranda</a>
        @endauth

        <div class="reveal flex items-center gap-3">
            <span class="h-8 w-1.5 rounded-full bg-brand-500"></span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Berita Sekolah</h1>
        </div>

        @if ($news->count() > 0)
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($news as $item)
                    @include('news._card', ['item' => $item, 'delay' => min($loop->index, 8) * 80])
                @endforeach
            </div>

            <div class="mt-8">
                {{ $news->links() }}
            </div>
        @else
            <div class="reveal mt-6 p-10 text-center bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"/></svg>
                </span>
                <p class="mt-3 text-gray-600 dark:text-gray-300">Belum ada berita.</p>
                <a href="{{ route('home') }}" class="mt-4 inline-block text-sm font-medium text-brand-600 dark:text-brand-300 hover:underline">&larr; Kembali ke beranda</a>
            </div>
        @endif
    </div>
</x-public-layout>