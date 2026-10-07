@props(['images'])
@php $total = $images->count(); @endphp

@if ($total > 0)
    <div x-data="{
            current: 0,
            total: {{ $total }},
            paused: false,
            touchX: 0,
            next() { this.current = (this.current + 1) % this.total },
            prev() { this.current = (this.current - 1 + this.total) % this.total },
            init() {
                if (this.total > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    setInterval(() => { if (!this.paused) this.next() }, 5000)
                }
            }
         }"
         @mouseenter="paused = true"
         @mouseleave="paused = false"
         @focusin="paused = true"
         @focusout="paused = false"
         @touchstart.passive="touchX = $event.changedTouches[0].clientX"
         @touchend="let d = $event.changedTouches[0].clientX - touchX; if (Math.abs(d) > 40) { d < 0 ? next() : prev() }"
         class="relative overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-800"
         role="region" aria-roledescription="carousel" aria-label="Foto berita">

        <div class="flex transition-transform duration-500 ease-out"
             :style="{ transform: 'translateX(-' + (current * 100) + '%)' }">
            @foreach ($images as $image)
                <div class="w-full shrink-0 aspect-video">
                    <img src="{{ $image->url() }}" alt="" loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                         class="h-full w-full object-cover">
                </div>
            @endforeach
        </div>

        @if ($total > 1)
            <button type="button" @click="prev()" aria-label="Gambar sebelumnya"
                    class="absolute left-2 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </button>

            <button type="button" @click="next()" aria-label="Gambar berikutnya"
                    class="absolute right-2 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            </button>

            <div class="absolute bottom-3 inset-x-0 flex justify-center gap-2">
                @foreach ($images as $image)
                    <button type="button" @click="current = {{ $loop->index }}"
                            :class="current === {{ $loop->index }} ? 'bg-white' : 'bg-white/50'"
                            class="h-2 w-2 rounded-full" aria-label="Gambar {{ $loop->iteration }}"></button>
                @endforeach
            </div>
        @endif
    </div>
@endif