<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Data Tagihan</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.300ms role="status" class="flex items-start gap-2 mb-4 p-3 bg-green-100 text-green-800 rounded-md"><svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="min-w-0 break-words">{{ session('success') }}</span></div>
            @endif

            @php
                $exportQuery = array_filter(request()->only(['q', 'status', 'kelas', 'semester', 'academic_year']));
            @endphp

            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="mb-4 flex flex-col sm:flex-row sm:flex-wrap gap-2">
                    <a href="{{ route('admin.invoices.create') }}"
                       class="inline-block px-4 py-2 text-center bg-brand-600 text-white rounded-md">Tambah Tagihan</a>
                    <a href="{{ route('admin.invoices.bulk.create') }}"
                       class="inline-block px-4 py-2 text-center border border-brand-600 text-brand-600 dark:text-brand-300 rounded-md hover:bg-brand-50 dark:hover:bg-gray-700">Tagihan Massal</a>
                    <a href="{{ route('admin.invoices.export', $exportQuery) }}"
                       class="inline-block px-4 py-2 text-center border border-gray-400 text-gray-700 dark:text-gray-200 rounded-md">Ekspor CSV</a>
                </div>

                {{-- Rekap --}}
                @php
                    $rekap = [
                        ['Total tagihan', $summary['semua'], 'text-gray-900 dark:text-gray-100'],
                        ['Lunas', $summary['lunas'], 'text-green-500'],
                        ['Belum lunas (termasuk terlambat)', $summary['belum'], 'text-yellow-500'],
                        ['Terlambat', $summary['terlambat'], 'text-red-500'],
                    ];
                @endphp
                <div class="mb-2 grid grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach ($rekap as [$label, $data, $warna])
                        <div class="flex h-full flex-col p-5 rounded-md bg-gray-50 dark:bg-gray-900">
                            <p class="min-h-[2.5rem] text-sm leading-5 text-gray-500 dark:text-gray-400">{{ $label }}</p>
                            <p class="mt-2 text-3xl font-bold tabular-nums {{ $warna }}">{{ $data['count'] }}</p>
                            <p class="mt-1 text-base font-medium tabular-nums text-gray-600 dark:text-gray-300 break-words">Rp {{ number_format($data['sum'], 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
                @if ($exportQuery)
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Rekap dan ekspor mengikuti filter yang sedang aktif.</p>
                @else
                    <div class="mb-4"></div>
                @endif

                {{-- Pencarian dan filter --}}
                <form method="GET" data-live-search action="{{ route('admin.invoices.index') }}"
                      class="mb-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Cari nama siswa atau tagihan"
                           class="w-full sm:w-72 text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">

                    <select name="status"
                            class="w-full sm:w-auto text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                        <option value="">Semua status</option>
                        <option value="belum_lunas" @selected(request('status') === 'belum_lunas')>Belum lunas</option>
                        <option value="terlambat" @selected(request('status') === 'terlambat')>Terlambat</option>
                        <option value="lunas" @selected(request('status') === 'lunas')>Lunas</option>
                    </select>

                    <select name="kelas"
                            class="w-full sm:w-auto text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                        <option value="">Semua kelas</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas }}" @selected(request('kelas') === (string) $kelas)>{{ $kelas }}</option>
                        @endforeach
                    </select>
                    <select name="academic_year"
                            class="w-full sm:w-auto text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                        <option value="">Semua tahun ajaran</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}" @selected(request('academic_year') === (string) $y)>Tahun ajaran {{ $y }}</option>
                        @endforeach
                    </select>

                    <select name="semester"
                            class="w-full sm:w-auto text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                        <option value="">Semua semester</option>
                        @foreach (\App\Models\Invoice::SEMESTERS as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(request('semester') === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="w-full sm:w-auto px-3 py-1.5 bg-brand-600 text-white text-sm rounded-md hover:bg-brand-500">
                        Cari
                    </button>

                    @if (request()->hasAny(['q', 'status', 'kelas', 'semester', 'academic_year']))
                        <a href="{{ route('admin.invoices.index') }}"
                           class="text-sm text-center sm:text-left text-gray-600 dark:text-gray-300 hover:underline">Reset</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="table-stack w-full text-left text-gray-800 dark:text-gray-200">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2">Siswa</th>
                                <th class="py-2">Tagihan</th>
                                <th class="py-2">Jumlah</th>
                                <th class="py-2">Jatuh Tempo</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-2" data-label="Siswa">{{ $invoice->student->name }}</td>
                                    <td class="py-2" data-label="Tagihan">{{ $invoice->title }}</td>
                                    <td class="py-2" data-label="Jumlah">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                    <td class="py-2" data-label="Jatuh Tempo">{{ $invoice->due_date->format('d/m/Y') }}</td>
                                    <td class="py-2" data-label="Status">
                                        <div>
                                            @if ($invoice->isPaid())
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Lunas</span>
                                            @elseif ($invoice->due_date->lt(today()))
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Terlambat</span>
                                            @else
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">Belum lunas</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-2" data-label="Aksi">
                                        <div class="flex flex-wrap justify-end md:justify-start gap-2">
                                            @unless ($invoice->isPaid())
                                                <form action="{{ route('admin.invoices.pay', $invoice) }}" x-data="{ busy: false }" @submit="busy = true" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" :disabled="busy" class="btn-act btn-act-success">Tandai Lunas</button>
                                                </form>
                                            @endunless
                                            @if ($invoice->isPaid())
                                                <a href="{{ route('receipts.show', $invoice) }}" target="_blank" class="btn-act btn-act-brand">Kuitansi</a>
                                            @endif
                                            <a href="{{ route('admin.invoices.edit', $invoice) }}"
                                               class="btn-act btn-act-neutral">Edit</a>
                                            <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST"
                                                  onsubmit="return confirm('Hapus tagihan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-act btn-act-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8"><div class="flex w-full flex-col items-center gap-2 text-center text-gray-500 dark:text-gray-400"><svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z"/></svg><span>Tidak ada tagihan yang cocok.</span></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/live-search.js') }}?v=1" defer></script>
</x-app-layout>