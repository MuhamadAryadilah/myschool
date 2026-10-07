<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tagihan Massal</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg"
                 x-data="{
                     target: {{ \Illuminate\Support\Js::from(old('target', 'semua')) }},
                     kelas: {{ \Illuminate\Support\Js::from(old('kelas', '')) }},
                     amount: {{ \Illuminate\Support\Js::from(old('amount', '')) }},
                     total: {{ $total }},
                     counts: {{ \Illuminate\Support\Js::from($countByKelas) }},
                     get count() { return this.target === 'semua' ? this.total : (this.counts[this.kelas] || 0) },
                     get nominal() { return new Intl.NumberFormat('id-ID').format(this.count * (parseInt(this.amount) || 0)) }
                 }">

                <form action="{{ route('admin.invoices.bulk.store') }}" method="POST" class="grid gap-4 md:grid-cols-2"
                      @submit="if (!confirm('Buat ' + count + ' tagihan sekarang?')) $event.preventDefault()">
                    @csrf

                    <div>
                        <span class="block text-sm text-gray-700 dark:text-gray-300">Tagih untuk</span>
                        <label class="mt-2 flex items-center gap-2 text-gray-800 dark:text-gray-200">
                            <input type="radio" name="target" value="semua" x-model="target">
                            Semua siswa (<span x-text="total"></span>)
                        </label>
                        <label class="mt-1 flex items-center gap-2 text-gray-800 dark:text-gray-200">
                            <input type="radio" name="target" value="kelas" x-model="target">
                            Satu kelas
                        </label>
                        @error('target') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="target === 'kelas'">
                        <label class="block text-sm text-gray-700 dark:text-gray-300">Kelas</label>
                        <select name="kelas" x-model="kelas" :disabled="target !== 'kelas'"
                                class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="">- Pilih kelas -</option>
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas }}">{{ $kelas }} ({{ $countByKelas[$kelas] ?? 0 }} siswa)</option>
                            @endforeach
                        </select>
                        @error('kelas') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-700 dark:text-gray-300">Nama Tagihan</label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               placeholder="Contoh: SPP November 2026"
                               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        @error('title') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 dark:text-gray-300">Jumlah per siswa (Rp)</label>
                        <input type="number" name="amount" min="1" x-model="amount"
                               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        @error('amount') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 dark:text-gray-300">Jatuh Tempo</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}"
                               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        @error('due_date') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 dark:text-gray-300">Semester (opsional)</label>
                        <select name="semester"
                                class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="">- Tanpa semester -</option>
                            @foreach (\App\Models\Invoice::SEMESTERS as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(old('semester') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('semester') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 dark:text-gray-300">Tahun ajaran (opsional)</label>
                        <input type="text" name="academic_year" value="{{ old('academic_year') }}" placeholder="Contoh: 2026/2027"
                               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        @error('academic_year') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2 p-3 rounded-md bg-gray-50 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300">
                        <p>
                            Akan dibuat <strong x-text="count"></strong> tagihan<span x-show="count > 0 && amount">
                            dengan total <strong>Rp <span x-text="nominal"></span></strong></span>.
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Siswa yang sudah punya tagihan dengan nama yang sama akan dilewati.
                        </p>
                    </div>

                    <div class="flex gap-3 md:col-span-2">
                        <button type="submit" :disabled="count === 0"
                                class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-md disabled:opacity-50">
                            Buat Tagihan
                        </button>
                        <a href="{{ route('admin.invoices.index') }}"
                           class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>