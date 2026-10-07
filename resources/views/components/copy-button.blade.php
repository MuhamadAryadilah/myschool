@props(['text', 'label' => 'Salin', 'done' => 'Tersalin'])

<button type="button"
        data-text="{{ $text }}"
        x-data="{
            copied: false,
            async copy(t) {
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(t);
                    } else {
                        const a = document.createElement('textarea');
                        a.value = t;
                        a.style.position = 'fixed';
                        a.style.opacity = '0';
                        document.body.appendChild(a);
                        a.select();
                        document.execCommand('copy');
                        document.body.removeChild(a);
                    }
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                } catch (e) {}
            }
        }"
        @click="copy($el.dataset.text)"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md border border-brand-600 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-gray-700']) }}>
    <svg x-show="!copied" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
    <svg x-show="copied" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    <span x-text="copied ? '{{ $done }}' : '{{ $label }}'">{{ $label }}</span>
</button>