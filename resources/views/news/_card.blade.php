<a href="{{ route('news.show', $item) }}"
   class="group reveal block h-full p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 border-t-4 border-t-brand-500 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition"
   style="animation-delay: {{ $delay ?? 0 }}ms">
    @if ($item->images->isNotEmpty())
        <div class="mb-4 h-40 overflow-hidden rounded-md">
            <img src="{{ $item->images->first()->url() }}" alt="" loading="lazy"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        </div>
    @endif
    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->created_at->translatedFormat('j F Y') }}</p>
    <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100 break-words">{{ $item->title }}</h3>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 break-words">{{ $item->excerpt() }}</p>
    <span class="mt-3 inline-block text-sm font-medium text-brand-600 dark:text-brand-300">Baca selengkapnya &rarr;</span>
</a>