<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Data Orang Tua</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.300ms role="status" class="flex items-start gap-2 mb-4 p-3 bg-green-100 text-green-800 rounded-md"><svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="min-w-0 break-words">{{ session('success') }}</span></div>
            @endif

            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <a href="{{ route('admin.parents.create') }}"
                   class="block sm:inline-block mb-4 px-4 py-2 text-center bg-brand-600 text-white rounded-md hover:bg-brand-500">Tambah Orang Tua</a>

                {{-- Pencarian --}}
                <form method="GET" data-live-search action="{{ route('admin.parents.index') }}"
                      class="mb-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Cari nama, email, telepon, atau nama anak"
                           class="w-full sm:w-72 text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">

                    <button type="submit"
                            class="w-full sm:w-auto px-3 py-1.5 bg-brand-600 text-white text-sm rounded-md hover:bg-brand-500">
                        Cari
                    </button>

                    @if (request()->filled('q'))
                        <a href="{{ route('admin.parents.index') }}"
                           class="text-sm text-center sm:text-left text-gray-600 dark:text-gray-300 hover:underline">Reset</a>
                    @endif
                </form>

                @if (session('reset_password'))
                @php $rp = session('reset_password'); @endphp
                <div x-data="{ copied: false }" role="status" class="mb-4 rounded-lg border-2 border-green-500/50 bg-green-50 p-4 text-sm text-green-900 dark:bg-green-500/10 dark:text-green-100">
                    <p class="font-semibold">Password baru untuk {{ $rp['name'] }}</p>
                    <p class="mt-1 break-all text-xs opacity-80">{{ $rp['email'] }}</p>
                    <p class="mt-2 text-2xl font-mono font-bold tracking-widest select-all">{{ $rp['password'] }}</p>
                    <p class="mt-1 text-xs">Password ini hanya tampil sekali. Salin atau kirim sekarang, lalu sampaikan agar diganti di menu Profil.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" class="btn-act btn-act-success"
                                @click="navigator.clipboard.writeText(@js($rp['password'])).then(() => copied = true)">
                            <span x-text="copied ? 'Tersalin' : 'Salin password'">Salin password</span>
                        </button>
                        @if ($rp['wa'])
                            <a target="_blank" rel="noopener" class="btn-act btn-act-brand"
                               href="{{ $rp['wa'] }}{{ str_contains($rp['wa'], '?') ? '&' : '?' }}text={{ rawurlencode('Halo '.$rp['name'].', password baru akun MySchool Anda: '.$rp['password'].' (email: '.$rp['email'].'). Silakan masuk, lalu ganti password di menu Profil.') }}">Kirim lewat WhatsApp</a>
                        @endif
                    </div>
                </div>
            @endif

                <div class="overflow-x-auto">
                    <table class="table-stack w-full text-left text-gray-800 dark:text-gray-200">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2">Nama</th>
                                <th class="py-2">Email</th>
                                <th class="py-2">Telepon</th>
                                <th class="py-2">Anak</th>
                                <th class="py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($parents as $parent)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-2" data-label="Nama">{{ $parent->name }}</td>
                                    <td class="py-2 break-all md:break-normal" data-label="Email">{{ $parent->email }}</td>
                                    <td class="py-2" data-label="Telepon">
                                        @if ($parent->phone)
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span>{{ $parent->phone }}</span>
                                                @if ($parent->whatsappUrl())
                                                    <a href="{{ $parent->whatsappUrl() }}" target="_blank" rel="noopener"
                                                       class="btn-act btn-act-neutral">WhatsApp</a>
                                                @endif
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2 break-words" data-label="Anak">
                                        {{ $parent->students->pluck('name')->join(', ') ?: '-' }}
                                    </td>
                                    <td class="py-2" data-label="Aksi">
                                        <div class="flex justify-end md:justify-start gap-4">
                                            <a href="{{ route('admin.parents.edit', $parent) }}"
                                               class="btn-act btn-act-neutral">Edit</a>
                                            <form action="{{ route('admin.parents.reset-password', $parent) }}" method="POST"
                                                  onsubmit="return confirm('Buat password sementara baru untuk {{ addslashes($parent->name) }}? Password lama tidak berlaku lagi.')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-act btn-act-brand">Reset Password</button>
                                            </form>
                                            <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST"
                                                  onsubmit="return confirm('Hapus akun orang tua ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-act btn-act-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8"><div class="flex w-full flex-col items-center gap-2 text-center text-gray-500 dark:text-gray-400"><svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z"/></svg><span>Tidak ada orang tua yang cocok.</span></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $parents->links() }}
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/live-search.js') }}?v=1" defer></script>
</x-app-layout>