@props(['flush' => false])

@php
    $tabs = [
        ['Pendaftar', 'admin.registrations.index', ['admin.registrations.*']],
        ['Pengaturan', 'admin.registration-settings.edit', ['admin.registration-settings.*']],
        ['Alur Setelah Daftar', 'admin.registration-steps.index', ['admin.registration-steps.*']],
    ];
@endphp

<div {{ $attributes->class(['mb-4', 'px-4 sm:px-0' => ! $flush]) }}>
    <div class="inline-flex max-w-full gap-1 overflow-x-auto rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
        @foreach ($tabs as [$label, $route, $active])
            @php $on = request()->routeIs(...$active); @endphp
            <a href="{{ route($route) }}" @if ($on) aria-current="page" @endif
               class="whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium transition {{ $on ? 'bg-gradient-to-r from-brand-600 to-sky-600 text-white' : 'text-gray-600 hover:bg-brand-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>