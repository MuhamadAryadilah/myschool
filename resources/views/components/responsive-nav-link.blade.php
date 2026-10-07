@props(['active' => false, 'icon' => null, 'badge' => 0])

@php
$classes = $active
    ? 'flex w-full items-center ps-3 pe-4 py-2 border-l-4 border-brand-400 dark:border-brand-600 text-start text-base font-medium text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-900/50 focus:outline-none focus:text-brand-800 dark:focus:text-brand-200 focus:bg-brand-100 dark:focus:bg-brand-900 focus:border-brand-700 dark:focus:border-brand-300 transition duration-150 ease-in-out'
    : 'flex w-full items-center ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <svg class="me-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
    @endif
    <span>{{ $slot }}</span>
    @if ($badge > 0)
        <span class="ms-2 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-yellow-400 px-1 text-xs font-semibold text-yellow-900">{{ $badge > 99 ? '99+' : $badge }}</span>
    @endif
</a>