@props(['invoice', 'open' => false])

@php
    $amount = (int) round($invoice->amount);
    $formatted = 'Rp '.number_format($amount, 0, ',', '.');
    $qris = config('payment.qris_image');
    $hasQris = $qris && file_exists(public_path($qris));
@endphp

<div x-data="{ tab: 'bank' }" {{ $attributes->merge(['class' => 'space-y-3 text-sm text-gray-700 dark:text-gray-300']) }}>

    <div class="flex items-center justify-between gap-3 rounded-xl bg-brand-50 dark:bg-brand-900/30 px-4 py-3.5">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Nominal transfer</p>
            <p class="text-2xl font-bold tabular-nums text-brand-800 dark:text-brand-200">{{ $formatted }}</p>
        </div>
        <x-copy-button :text="$amount" label="Salin" class="shrink-0" />
    </div>

    @if ($hasQris)
        <div class="inline-flex rounded-full bg-gray-200/70 dark:bg-gray-700 p-1" role="tablist">
            <button type="button" role="tab" @click="tab = 'bank'"
                    :class="tab === 'bank' ? 'bg-white dark:bg-gray-900 shadow-sm text-brand-700 dark:text-brand-200' : 'text-gray-600 dark:text-gray-300'"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition">Transfer bank</button>
            <button type="button" role="tab" @click="tab = 'qris'"
                    :class="tab === 'qris' ? 'bg-white dark:bg-gray-900 shadow-sm text-brand-700 dark:text-brand-200' : 'text-gray-600 dark:text-gray-300'"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition">QRIS</button>
        </div>
    @endif

    <div x-show="tab === 'bank'" class="space-y-2">
        <p class="text-xs text-gray-500 dark:text-gray-400">Atas nama <strong class="text-gray-800 dark:text-gray-200">{{ config('payment.account_name') }}</strong></p>
        @foreach (config('payment.banks') as $bank)
            <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-gray-800 p-3 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-xs font-bold text-white">{{ mb_strtoupper(mb_substr($bank['name'], 0, 4)) }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $bank['name'] }}</p>
                    <p class="text-lg font-bold tabular-nums tracking-wide text-gray-900 dark:text-gray-100 break-all">{{ $bank['number'] }}</p>
                </div>
                <x-copy-button :text="$bank['number']" label="Salin" class="shrink-0" />
            </div>
        @endforeach
    </div>

    @if ($hasQris)
        <div x-show="tab === 'qris'" x-cloak class="flex flex-col items-center gap-3 rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
            <a href="{{ asset($qris) }}" target="_blank" class="block w-48 rounded-xl bg-white p-2 ring-1 ring-gray-200">
                <img src="{{ asset($qris) }}" alt="Kode QRIS pembayaran" class="w-full">
            </a>
            <p class="text-center text-xs text-gray-500 dark:text-gray-400">Scan, lalu ketik nominal <strong>{{ $formatted }}</strong> sendiri. QRIS ini tidak mengunci nominal.</p>
        </div>
    @endif
</div>