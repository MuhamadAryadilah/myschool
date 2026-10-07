<x-public-layout :title="$news->title">
    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <a href="{{ route('news.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-brand-600 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-gray-700 transition">&larr; Semua berita</a>

        @if ($news->status !== 'published')
            <div class="mt-4 p-3 bg-accent-100 text-accent-600 rounded-md text-sm">
                Pratinjau: berita ini masih draft dan belum tampil untuk publik.
            </div>
        @endif

        <div class="reveal mt-4 p-5 sm:p-8 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 border-t-4 border-t-brand-500 shadow-sm">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight break-words text-gray-900 dark:text-gray-100">{{ $news->title }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ $news->created_at->translatedFormat('j F Y') }}@if ($news->author) &middot; {{ $news->author }}@endif
            </p>

            @if ($news->images->isNotEmpty())
                <div class="mt-6">
                    <x-news-carousel :images="$news->images" />
                </div>
            @endif

            <div class="mt-6 text-base sm:text-lg leading-relaxed break-words text-gray-800 dark:text-gray-200">
                {!! nl2br(e($news->content)) !!}
            </div>
        </div>
    </article>

    @if ($others->isNotEmpty())
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="flex items-center gap-3">
                <span class="h-6 w-1.5 rounded-full bg-brand-500"></span>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Berita lainnya</h2>
            </div>
            <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($others as $item)
                    @include('news._card', ['item' => $item, 'delay' => $loop->index * 80])
                @endforeach
            </div>
        </section>
    @endif
</x-public-layout>