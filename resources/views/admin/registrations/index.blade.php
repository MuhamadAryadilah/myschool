<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Pendaftaran Siswa Baru</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.300ms role="status" class="flex items-start gap-2 mb-4 p-3 bg-green-100 text-green-800 rounded-md"><svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="min-w-0 break-words">{{ session('success') }}</span></div>
            @endif

            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">

                {{-- Tab status --}}
                <div class="mb-4 flex flex-wrap gap-2 text-sm">
                    @php
                        $tabs = ['' => 'Semua', 'menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'];
                        $current = is_string(request('status')) ? request('status') : '';
                    @endphp
                    @foreach ($tabs as $key => $label)
                        @php
                            $count = $key === '' ? $counts->sum() : ($counts[$key] ?? 0);
                            $active = $current === (string) $key;
                        @endphp
                        <a href="{{ route('admin.registrations.index', array_filter(['status' => $key, 'q' => is_string(request('q')) ? request('q') : null])) }}"
                           class="px-3 py-1.5 rounded-md border {{ $active ? 'bg-brand-600 border-brand-600 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}">
                            {{ $label }} ({{ $count }})
                        </a>
                    @endforeach
                </div>

                {{-- Pencarian --}}
                <form method="GET" action="{{ route('admin.registrations.index') }}"
                      class="mb-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2">
                    @if ($current !== '')
                        <input type="hidden" name="status" value="{{ $current }}">
                    @endif
                    <input type="text" name="q" value="{{ is_string(request('q')) ? request('q') : '' }}"
                           placeholder="Cari nama siswa, orang tua, atau email"
                           class="w-full sm:w-72 text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                    <button type="submit"
                            class="w-full sm:w-auto px-3 py-1.5 bg-brand-600 text-white text-sm rounded-md hover:bg-brand-500">Cari</button>
                    @if (request()->hasAny(['q', 'status']))
                        <a href="{{ route('admin.registrations.index') }}"
                           class="text-sm text-center sm:text-left text-gray-600 dark:text-gray-300 hover:underline">Reset</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="table-stack w-full text-left text-gray-800 dark:text-gray-200">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2">Nomor</th>
                                <th class="py-2">Calon siswa</th>
                                <th class="py-2">Orang tua/wali</th>
                                <th class="py-2">Email</th>
                                <th class="py-2">Tanggal</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($registrations as $registration)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-2" data-label="Nomor">{{ $registration->reference() }}</td>
                                    <td class="py-2" data-label="Calon siswa">{{ $registration->student_name }}</td>
                                    <td class="py-2" data-label="Orang tua/wali">{{ $registration->guardian_name }}</td>
                                    <td class="py-2 break-all md:break-normal" data-label="Email">{{ $registration->email }}</td>
                                    <td class="py-2" data-label="Tanggal">{{ $registration->created_at->format('d/m/Y') }}</td>
                                    <td class="py-2" data-label="Status">
                                        @if ($registration->status === 'diterima')
                                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Diterima</span>
                                        @elseif ($registration->status === 'ditolak')
                                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Ditolak</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="py-2" data-label="Aksi">
                                        <a href="{{ route('admin.registrations.show', $registration) }}"
                                           class="btn-act btn-act-brand">Periksa</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-8"><div class="flex w-full flex-col items-center gap-2 text-center text-gray-500 dark:text-gray-400"><svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z"/></svg><span>Tidak ada pendaftaran yang cocok.</span></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $registrations->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>