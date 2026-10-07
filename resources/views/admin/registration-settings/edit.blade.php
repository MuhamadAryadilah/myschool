<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Aturan Pendaftaran</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @include('admin.registration-settings._alerts')

            {{-- Batas usia --}}
            <form method="POST" action="{{ route('admin.registration-settings.update') }}"
                  class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                @csrf
                @method('PUT')

                <div class="flex items-start gap-3 border-b border-gray-100 bg-gradient-to-r from-brand-50 to-transparent px-4 py-4 dark:border-gray-700 dark:from-brand-900/40 dark:to-transparent sm:px-6">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Batas usia pendaftar</h3>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Pendaftar di luar rentang ini ditolak otomatis oleh formulir. Kosongkan kedua kolom kalau tidak ingin membatasi usia.</p>
                    </div>
                </div>

                <div class="space-y-5 p-4 sm:p-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usia minimal</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="number" name="min_age" min="1" max="30" value="{{ old('min_age', $limits['min']) }}" placeholder="Tanpa batas"
                                       class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                <span class="shrink-0 text-sm text-gray-500 dark:text-gray-400">tahun</span>
                            </div>
                            @error('min_age') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usia maksimal</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="number" name="max_age" min="1" max="30" value="{{ old('max_age', $limits['max']) }}" placeholder="Tanpa batas"
                                       class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                <span class="shrink-0 text-sm text-gray-500 dark:text-gray-400">tahun</span>
                            </div>
                            @error('max_age') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hitung usia per tanggal <span class="font-normal text-gray-500 dark:text-gray-400">(opsional)</span></label>
                        <input type="date" name="age_reference_date" value="{{ old('age_reference_date', $limits['date']) }}"
                               class="mt-1 w-full sm:w-64 rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Contoh: 1 Juli di awal tahun ajaran. Kalau kosong, usia dihitung per hari pendaftaran.</p>
                        @error('age_reference_date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    @if ($description)
                        <div class="rounded-md bg-brand-50 p-3 text-sm text-brand-800 dark:bg-brand-900/30 dark:text-brand-100">
                            <span class="font-medium">Yang dilihat pendaftar:</span> {{ $description }}
                        </div>
                    @endif

                    <button type="submit" class="w-full sm:w-auto rounded-md bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-500">Simpan</button>
                </div>
            </form>

            {{-- Alur --}}
            <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="flex items-start gap-3 border-b border-gray-100 bg-gradient-to-r from-brand-50 to-transparent px-4 py-4 dark:border-gray-700 dark:from-brand-900/40 dark:to-transparent sm:px-6">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zM3.75 12h.007v.008H3.75V12zm0 5.25h.007v.008H3.75v-.008z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Alur setelah mendaftar</h3>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Langkah-langkah yang dilihat calon orang tua (bayar formulir, jadwal tes, dan seterusnya).</p>
                    </div>
                </div>
                <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">Saat ini ada {{ $stepCount }} langkah</span>
                    <a href="{{ route('admin.registration-steps.index') }}"
                       class="rounded-md bg-brand-600 px-5 py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-500">Atur alur pendaftaran</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>