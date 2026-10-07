<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Verifikasi Pembayaran
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="flex justify-end">
                <a href="{{ route('admin.payments.export') }}"
                   class="px-4 py-2 text-sm text-center border border-gray-400 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">Ekspor CSV</a>
            </div>

            @if (session('status'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.300ms role="status" class="flex items-start gap-2 p-4 bg-green-100 text-green-800 rounded-md"><span class="min-w-0 break-words">{{ session('status') }}</span></div>
            @endif
            @if ($errors->any())
                <div role="alert" class="p-4 bg-red-100 text-red-800 rounded-md">{{ $errors->first() }}</div>
            @endif

            @forelse ($payments as $payment)
                @php
                    $selisih = $payment->amount_paid !== null ? $payment->amountDifference() : null;
                    $beda = $selisih !== null && $selisih !== 0;
                @endphp
                <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 shadow-sm rounded-lg text-gray-800 dark:text-gray-200">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold break-words">{{ $payment->invoice->student->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 break-words">{{ $payment->invoice->title }}</p>
                        </div>
                        <div class="shrink-0">
                            @if ($payment->status === 'menunggu')
                                <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">Menunggu</span>
                            @elseif ($payment->status === 'diterima')
                                <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Diterima</span>
                            @else
                                <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Ditolak</span>
                            @endif
                        </div>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Tagihan</dt>
                            <dd class="font-semibold tabular-nums">Rp {{ number_format($payment->invoice->amount, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Dibayar</dt>
                            <dd class="font-semibold tabular-nums">
                                @if ($payment->amount_paid !== null)
                                    Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                                @else
                                    <span class="font-normal text-gray-500 dark:text-gray-400">Tidak diisi</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Diupload oleh</dt>
                            <dd class="break-words">{{ $payment->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Waktu</dt>
                            <dd>{{ $payment->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>

                    @if ($beda)
                        <p class="mt-3 p-3 text-sm rounded-md bg-yellow-50 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300">
                            Nominal berbeda: {{ $selisih < 0 ? 'kurang' : 'lebih' }} <strong>Rp {{ number_format(abs($selisih), 0, ',', '.') }}</strong> dari tagihan.
                        </p>
                    @elseif ($selisih === 0)
                        <p class="mt-3 text-sm text-green-600 dark:text-green-400">Nominal sesuai tagihan.</p>
                    @elseif ($selisih === null)
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Nominal tidak diisi.</p>
                    @endif

                    @if ($payment->note)
                        <p class="mt-3 text-sm"><span class="font-medium">Catatan orang tua:</span> {{ $payment->note }}</p>
                    @endif

                    @if ($payment->status === 'ditolak')
                        <div class="mt-3 p-3 text-sm rounded-md bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400">
                            <span class="font-medium">Alasan penolakan:</span> {{ $payment->admin_note ?? 'Tanpa catatan' }}
                        </div>
                    @endif

                    <a href="{{ route('payments.proof', $payment) }}" target="_blank"
                       class="btn-proof mt-4 inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-md border border-brand-500/40 bg-brand-50 px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-100 dark:border-brand-400/30 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/20">
                        Lihat bukti bayar
                    </a>

                    @if ($payment->status === 'menunggu')
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700" x-data="{ tolak: false }">
                            <div x-show="!tolak" class="grid grid-cols-2 gap-3">
                                <form method="POST" action="{{ route('admin.payments.approve', $payment) }}"
                                      @if ($beda) onsubmit="return confirm('Nominal yang dibayar berbeda dari tagihan. Tetap terima pembayaran ini?')" @endif
                                      x-data="{ busy: false }" @submit="busy = true">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" :disabled="busy"
                                            class="w-full min-h-[2.75rem] rounded-md bg-green-600 px-5 text-sm font-semibold text-white hover:bg-green-500 disabled:opacity-60">Terima</button>
                                </form>
                                <button type="button" @click="tolak = true"
                                        class="w-full min-h-[2.75rem] rounded-md bg-red-600 px-5 text-sm font-semibold text-white hover:bg-red-500">Tolak</button>
                            </div>

                            <form x-show="tolak" x-cloak method="POST" action="{{ route('admin.payments.reject', $payment) }}"
                                  x-data="{ busy: false }" @submit="busy = true" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <label class="block text-sm text-gray-600 dark:text-gray-400">Alasan penolakan (akan dibaca orang tua)</label>
                                <input type="text" name="admin_note" required placeholder="Misal: foto buram, nominal tidak sesuai"
                                       class="block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                                <div class="grid grid-cols-2 gap-3">
                                    <button type="submit" :disabled="busy"
                                            class="min-h-[2.75rem] rounded-md bg-red-600 px-5 text-sm font-semibold text-white hover:bg-red-500 disabled:opacity-60">Kirim penolakan</button>
                                    <button type="button" @click="tolak = false"
                                            class="min-h-[2.75rem] rounded-md border border-gray-300 dark:border-gray-600 px-5 text-sm font-medium text-gray-700 dark:text-gray-200">Batal</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <p class="text-gray-500 dark:text-gray-400">Belum ada pembayaran masuk.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>