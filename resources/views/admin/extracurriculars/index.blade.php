<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Ekstrakurikuler</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('admin.extracurriculars._alerts')

            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('admin.extracurriculars.create') }}"
                       class="block sm:inline-block px-4 py-2 text-center bg-brand-600 hover:bg-brand-500 text-white rounded-md">Tambah Ekstrakurikuler</a>

                    @if ($unassigned > 0)
                        <a href="{{ route('admin.students.index', ['eskul' => 'none']) }}"
                           class="text-sm text-center sm:text-right text-brand-600 dark:text-brand-300 hover:underline">{{ $unassigned }} siswa belum punya eskul</a>
                    @else
                        <p class="text-sm text-center sm:text-right text-gray-600 dark:text-gray-300">Semua siswa sudah punya eskul.</p>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="table-stack w-full text-left text-gray-800 dark:text-gray-200">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2">Nama</th>
                                <th class="py-2">Jadwal</th>
                                <th class="py-2">Anggota</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Kelola</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($extracurriculars as $eskul)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-2" data-label="Nama">
                                        <a href="{{ route('admin.extracurriculars.show', $eskul) }}"
                                           class="font-medium text-brand-600 dark:text-brand-300 hover:underline">{{ $eskul->name }}</a>
                                    </td>
                                    <td class="py-2" data-label="Jadwal">{{ $eskul->schedule ?: '-' }}</td>
                                    <td class="py-2" data-label="Anggota">{{ $eskul->students_count }}</td>
                                    <td class="py-2" data-label="Status">
                                        @if ($eskul->is_active)
                                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Aktif</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-gray-200 text-gray-700 dark:bg-gray-600/40 dark:text-gray-300">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="py-2" data-label="Kelola">
                                        <div class="flex justify-end md:justify-start gap-4">
                                            <a href="{{ route('admin.extracurriculars.show', $eskul) }}"
                                               class="btn-act btn-act-brand">Anggota</a>
                                            <a href="{{ route('admin.extracurriculars.edit', $eskul) }}"
                                               class="btn-act btn-act-neutral">Edit</a>
                                            @if ($eskul->students_count === 0)
                                                <form action="{{ route('admin.extracurriculars.destroy', $eskul) }}" method="POST"
                                                      onsubmit="return confirm('Hapus ekstrakurikuler ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-act btn-act-danger">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-gray-500 dark:text-gray-400">Belum ada ekstrakurikuler.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>