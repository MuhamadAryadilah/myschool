@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

    $semuaTagihan = $students->flatMap(fn ($s) => $s->invoices);
    $belumLunas   = $semuaTagihan->filter(fn ($i) => ! $i->isPaid());
    $sisaTotal    = $belumLunas->sum('amount');
    $jumlahTelat  = $belumLunas->filter(fn ($i) => $i->due_date->isPast())->count();
    $jumlahMenunggu = $belumLunas->filter(function ($i) {
        $p = $i->payments->sortByDesc('id')->first();
        return $p && $p->status === 'menunggu';
    })->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.300ms role="status" class="flex items-start gap-2 p-4 bg-green-100 text-green-800 rounded-md"><svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="min-w-0 break-words">{{ session('status') }}</span></div>
            @endif
            @if ($errors->any())
                <div role="alert" class="flex items-start gap-2 p-4 bg-red-100 text-red-800 rounded-md"><svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg><ul class="min-w-0 break-words">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            {{-- Banner sapaan --}}
            <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 px-5 py-6 sm:px-8 sm:py-8 text-white shadow-sm">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
                <div class="absolute -right-2 top-16 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="relative">
                    <p class="text-sm text-white/80">{{ now()->translatedFormat('l, j F Y') }}</p>
                    <h3 class="mt-1 text-xl sm:text-2xl font-bold break-words">Halo, {{ auth()->user()->name }}</h3>
                    <p class="mt-1 text-sm text-white/80">Pantau tagihan dan pembayaran anak Anda di sini.</p>
                </div>
            </div>

            @if ($students->isNotEmpty())
                {{-- Ringkasan --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                    <div class="relative col-span-2 sm:col-span-1 overflow-hidden p-4 sm:p-5 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                        <span class="absolute inset-y-0 left-0 w-1 bg-brand-500"></span>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Sisa tagihan</p>
                        <p class="mt-2 text-xl sm:text-2xl font-bold break-words {{ $sisaTotal > 0 ? 'text-gray-900 dark:text-gray-100' : 'text-green-500' }}">{{ $rp($sisaTotal) }}</p>
                    </div>
                    <div class="relative overflow-hidden p-4 sm:p-5 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                        <span class="absolute inset-y-0 left-0 w-1 bg-red-400"></span>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Lewat jatuh tempo</p>
                        <p class="mt-2 text-xl sm:text-2xl font-bold {{ $jumlahTelat > 0 ? 'text-red-500' : 'text-gray-900 dark:text-gray-100' }}">{{ $jumlahTelat }}</p>
                    </div>
                    <div class="relative overflow-hidden p-4 sm:p-5 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                        <span class="absolute inset-y-0 left-0 w-1 bg-yellow-400"></span>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Sedang dicek admin</p>
                        <p class="mt-2 text-xl sm:text-2xl font-bold {{ $jumlahMenunggu > 0 ? 'text-yellow-500' : 'text-gray-900 dark:text-gray-100' }}">{{ $jumlahMenunggu }}</p>
                    </div>
                </div>
            @endif


            @php
                $waNumber = preg_replace('/\D+/', '', (string) config('school.whatsapp'));
                if ($waNumber !== '' && str_starts_with($waNumber, '0')) { $waNumber = '62'.substr($waNumber, 1); }
            @endphp
            @if ($waNumber !== '')
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Ada kendala dengan tagihan atau pembayaran?</p>
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                       class="inline-block px-4 py-2 text-center text-sm font-medium border border-brand-600 text-brand-700 dark:text-brand-300 rounded-md hover:bg-brand-50 dark:hover:bg-gray-700 transition">Hubungi sekolah</a>
                </div>
            @endif
            @forelse ($students as $student)
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg">

                    {{-- Kepala kartu anak --}}
                    <div class="flex items-center gap-3 px-4 py-4 sm:px-6 bg-gradient-to-r from-brand-50 to-transparent dark:from-brand-900/40 dark:to-transparent border-b border-gray-100 dark:border-gray-700">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white font-bold">
                            {{ mb_strtoupper(mb_substr($student->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 break-words">{{ $student->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                NIS: {{ $student->nis }} &middot; Kelas: {{ $student->kelas }}
                            </p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6" x-data="{ open: null }">
                        @php
                            $invoices = $student->invoices->sortBy(fn ($i) => ($i->isPaid() ? '1' : '0').$i->due_date->format('Ymd'));
                            $totalUnpaid = $invoices->reject(fn ($i) => $i->isPaid())->sum('amount');
                        @endphp

                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Tagihan</h3>

                        @forelse ($invoices as $invoice)
                            @php
                                $lastPayment = $invoice->payments->sortByDesc('id')->first();
                                $bar = $invoice->isPaid() ? 'bg-green-400' : ($invoice->due_date->isPast() ? 'bg-red-400' : 'bg-yellow-400');
                            @endphp

                            <div class="relative mb-3 p-4 pl-5 sm:p-5 sm:pl-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden text-gray-800 dark:text-gray-200">
                                <span class="absolute inset-y-0 left-0 w-1.5 {{ $bar }}"></span>

                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="font-semibold break-words">{{ $invoice->title }}</p>
                                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Jatuh tempo: {{ $invoice->due_date->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-lg font-bold tabular-nums whitespace-nowrap">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</p>
                                        @if ($invoice->isPaid())
                                            <span class="mt-1 inline-block px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400">Lunas</span>
                                        @elseif ($invoice->due_date->isPast())
                                            <span class="mt-1 inline-block px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400">Terlambat</span>
                                        @else
                                            <span class="mt-1 inline-block px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">Belum lunas</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($invoice->isPaid())
                                    <div class="mt-3">
                                        <a href="{{ route('receipts.show', $invoice) }}" target="_blank" class="btn-act btn-act-brand">Unduh kuitansi</a>
                                    </div>
                                @elseif ($lastPayment && $lastPayment->status === 'menunggu')
                                    <p class="mt-4 p-3 text-sm rounded-md bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400">
                                        Bukti bayar sudah terkirim. Mohon tunggu, admin sedang mengecek.
                                    </p>
                                @else
                                    @if ($lastPayment && $lastPayment->status === 'ditolak')
                                        <div role="alert" class="mt-4 p-3 text-sm rounded-md bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                            <p class="font-semibold">Bukti bayar sebelumnya ditolak</p>
                                            @if (filled($lastPayment->admin_note))
                                                <p class="mt-0.5">Alasan: {{ $lastPayment->admin_note }}</p>
                                            @endif
                                        </div>
                                    @endif

                                    <button type="button" @click="open = {{ $invoice->id }}"
                                            x-show="open !== {{ $invoice->id }}"
                                            class="mt-4 w-full px-5 py-3.5 text-sm font-semibold rounded-xl bg-brand-600 text-white shadow-sm hover:bg-brand-500 active:scale-[0.99] transition">
                                        {{ ($lastPayment && $lastPayment->status === 'ditolak') ? 'Upload ulang bukti' : 'Bayar sekarang' }}
                                    </button>

                                    <div x-show="open === {{ $invoice->id }}" x-cloak class="mt-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 p-4 sm:p-5">

                                        <div class="relative flex gap-3 pb-6">
                                            <span class="absolute left-3 top-7 bottom-0 w-px bg-gray-300 dark:bg-gray-600"></span>
                                            <span class="relative z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">1</span>
                                            <div class="min-w-0 flex-1">
                                                <p class="mb-3 text-sm font-semibold">Transfer ke rekening sekolah</p>
                                                <x-payment-instructions :invoice="$invoice" />
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('payments.store', $invoice) }}"
                                              enctype="multipart/form-data"
                                              x-data="{ busy: false, more: false, fileName: '' }" @submit="busy = true"
                                              class="flex gap-3">
                                            @csrf
                                            <span class="relative z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">2</span>
                                            <div class="min-w-0 flex-1 space-y-4">
                                                <p class="text-sm font-semibold">Kirim foto bukti transfer</p>

                                                <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 border-dashed bg-white dark:bg-gray-800 px-4 py-6 text-center transition hover:border-brand-500"
                                                       :class="fileName ? 'border-brand-500' : 'border-gray-300 dark:border-gray-600'">
                                                    <svg class="h-8 w-8 text-brand-600 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                                    <span class="break-all text-sm font-medium text-gray-800 dark:text-gray-100" x-text="fileName || 'Ketuk untuk pilih foto bukti'">Ketuk untuk pilih foto bukti</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400" x-show="!fileName">JPG, PNG, atau PDF</span>
                                                    <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required class="sr-only" oninvalid="this.setCustomValidity('Foto bukti transfer belum dipilih. Silakan pilih file JPG, PNG, atau PDF.')" oninput="this.setCustomValidity('')"
                                                           @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                                                </label>

                                                <input type="hidden" name="amount_paid" value="{{ number_format($invoice->amount, 0, ',', '.') }}">

                                                <div>
                                                    <button type="button" x-show="!more" @click="more = true" class="text-sm text-brand-600 dark:text-brand-300 underline">+ Tambah catatan (opsional)</button>
                                                    <input type="text" name="note" x-show="more" x-cloak placeholder="Catatan (opsional), misal: transfer dari BCA a.n. Ayah"
                                                           class="block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                                                </div>
                                                <div>
                                                    <button type="submit" :disabled="busy"
                                                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 disabled:opacity-60 disabled:cursor-not-allowed">
                                                        
                                                        <span x-text="busy ? 'Memproses...' : 'Kirim bukti bayar'">Kirim bukti bayar</span>
                                                    </button>
                                                    <button type="button" @click="open = null" class="mt-3 block w-full text-center text-sm text-gray-500 dark:text-gray-400 underline">Tutup</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>

                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada tagihan.</p>
                        @endforelse

                        @if ($totalUnpaid > 0)
                            <p class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                Total belum dibayar: Rp {{ number_format($totalUnpaid, 0, ',', '.') }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <p class="text-gray-500 dark:text-gray-400">
                        Belum ada data anak yang terhubung dengan akun ini.
                    </p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>