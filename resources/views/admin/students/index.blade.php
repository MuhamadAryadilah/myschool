<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Data Siswa</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.300ms role="status" class="flex items-start gap-2 mb-4 p-3 bg-brand-100 text-brand-800 rounded-md"><svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="min-w-0 break-words">{{ session('success') }}</span></div>
            @endif

            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <a href="{{ route('admin.students.create') }}"
                   class="block sm:inline-block mb-4 px-4 py-2 text-center bg-brand-600 hover:bg-brand-500 text-white rounded-md">Tambah Siswa</a>
                <a href="{{ route('admin.students.export', array_filter(request()->only(['q', 'kelas', 'eskul']))) }}"
                   class="block sm:inline-block mb-4 sm:ms-2 px-4 py-2 text-center border border-gray-400 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">Ekspor CSV</a>

                <form method="GET" data-live-search action="{{ route('admin.students.index') }}"
                      class="mb-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Cari nama, NIS, atau orang tua"
                           class="w-full sm:w-auto text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">

                    <select name="eskul"
                            class="w-full sm:w-auto text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                        <option value="">Semua eskul</option>
                        <option value="none" @selected(request('eskul') === 'none')>Belum punya eskul</option>
                        @foreach ($extracurriculars as $eskulFilter)
                            <option value="{{ $eskulFilter->id }}" @selected(request('eskul') === (string) $eskulFilter->id)>{{ $eskulFilter->name }}</option>
                        @endforeach
                    </select>

                    <select name="kelas"
                            class="w-full sm:w-auto text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                        <option value="">Semua kelas</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas }}" @selected(request('kelas') === (string) $kelas)>{{ $kelas }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="w-full sm:w-auto px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white text-sm rounded-md">
                        Cari
                    </button>

                    @if (request()->hasAny(['q', 'kelas', 'eskul']))
                        <a href="{{ route('admin.students.index') }}"
                           class="text-sm text-center sm:text-left text-gray-600 dark:text-gray-300 hover:underline">Reset</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="table-stack w-full text-left text-gray-800 dark:text-gray-200">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2">NIS</th>
                                <th class="py-2">Nama</th>
                                <th class="py-2">Kelas</th>
                                <th class="py-2">Eskul</th>
                                <th class="py-2">Orang Tua</th>
                                <th class="py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-2" data-label="NIS">{{ $student->nis }}</td>
                                    <td class="py-2" data-label="Nama">{{ $student->name }}</td>
                                    <td class="py-2" data-label="Kelas">{{ $student->kelas }}</td>
                                    <td class="py-2" data-label="Eskul">{{ $student->extracurriculars->pluck('name')->join(', ') ?: '-' }}</td>
                                    <td class="py-2" data-label="Orang Tua">{{ $student->parent?->name ?? '-' }}</td>
                                    <td class="py-2" data-label="Aksi">
                                        <div class="flex justify-end md:justify-start gap-4">
                                            <a href="{{ route('admin.students.edit', $student) }}"
                                               class="btn-act btn-act-neutral">Edit</a>
                                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                                                  onsubmit="return confirm('Hapus siswa ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-act btn-act-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8"><div class="flex w-full flex-col items-center gap-2 text-center text-gray-500 dark:text-gray-400"><svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z"/></svg><span>Tidak ada siswa yang cocok.</span></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/live-search.js') }}?v=1" defer></script>
</x-app-layout>