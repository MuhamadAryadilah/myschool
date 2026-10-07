@props(['active' => false, 'icon' => null, 'badge' => 0])

@php
$classes = $active
    ? 'group relative inline-flex h-16 items-center px-1 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none transition-colors duration-150'
    : 'group relative inline-flex h-16 items-center px-1 text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <svg class="me-1.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
    @endif
    {{ $slot }}
    @if ($badge > 0)
        <span class="ms-1.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-yellow-400 px-1 text-xs font-semibold text-yellow-900">{{ $badge > 99 ? '99+' : $badge }}</span>
    @endif
    <span aria-hidden="true"
          class="pointer-events-none absolute inset-x-0 bottom-0 h-0.5 origin-center bg-brand-500 transition-transform duration-200 motion-reduce:transition-none {{ $active ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100 group-focus:scale-x-100' }}"></span>
</a>