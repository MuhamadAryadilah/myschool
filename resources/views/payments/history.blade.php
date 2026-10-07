<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Riwayat Pembayaran
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">

                <div class="overflow-x-auto">
                    <table class="table-stack w-full text-left text-gray-800 dark:text-gray-200">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2">Tanggal upload</th>
                                <th class="py-2">Siswa</th>
                                <th class="py-2">Tagihan</th>
                                <th class="py-2">Jumlah</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr class="border-b border-gray-200 dark:border-gray-700 align-top">
                                    <td class="py-2" data-label="Tanggal upload">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-2" data-label="Siswa">{{ $payment->invoice->student->name }}</td>
                                    <td class="py-2" data-label="Tagihan">{{ $payment->invoice->title }}</td>
                                    <td class="py-2" data-label="Jumlah">Rp {{ number_format($payment->invoice->amount, 0, ',', '.') }}</td>
                                    <td class="py-2" data-label="Status">
                                        <div>
                                            @if ($payment->status === 'menunggu')
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">Menunggu verifikasi</span>
                                            @elseif ($payment->status === 'diterima')
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Diterima</span>
                                            @else
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Ditolak</span>
                                                @if ($payment->admin_note)
                                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">Alasan: {{ $payment->admin_note }}</p>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-2" data-label="Bukti">
                                        <a href="{{ route('payments.proof', $payment) }}" target="_blank"
                                           class="text-brand-500 hover:underline">Lihat</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-gray-500 dark:text-gray-400">
                                        Belum ada bukti bayar yang diupload.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>