@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-3">
        {{-- HP: sebelumnya / berikutnya --}}
        <div class="flex flex-1 justify-between gap-3 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500">&larr; Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">&larr; Sebelumnya</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Berikutnya &rarr;</a>
            @else
                <span class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500">Berikutnya &rarr;</span>
            @endif
        </div>

        {{-- Layar besar: ringkasan + angka --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between sm:gap-4">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Menampilkan <span class="font-semibold">{{ $paginator->firstItem() }}</span>&ndash;<span class="font-semibold">{{ $paginator->lastItem() }}</span>
                dari <span class="font-semibold">{{ $paginator->total() }}</span>
            </p>

            <div class="inline-flex flex-wrap items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="rounded-md px-3 py-1.5 text-sm text-gray-400 dark:text-gray-500" aria-hidden="true">&larr;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" class="rounded-md px-3 py-1.5 text-sm text-gray-700 hover:bg-brand-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-200 dark:hover:bg-gray-700">&larr;</a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 py-1.5 text-sm text-gray-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="min-w-[2.25rem] rounded-md bg-gradient-to-r from-brand-600 to-sky-600 px-3 py-1.5 text-center text-sm font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="Halaman {{ $page }}" class="min-w-[2.25rem] rounded-md px-3 py-1.5 text-center text-sm text-gray-700 hover:bg-brand-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-200 dark:hover:bg-gray-700">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya" class="rounded-md px-3 py-1.5 text-sm text-gray-700 hover:bg-brand-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-200 dark:hover:bg-gray-700">&rarr;</a>
                @else
                    <span class="rounded-md px-3 py-1.5 text-sm text-gray-400 dark:text-gray-500" aria-hidden="true">&rarr;</span>
                @endif
            </div>
        </div>
    </nav>
@endif