<x-mail::message>
# Bukti bayar baru

{{ $payment->user->name }} mengunggah bukti bayar yang menunggu verifikasi.

<x-mail::table>
| | |
|:--|:--|
| Siswa | {{ $payment->invoice->student->name }} ({{ $payment->invoice->student->kelas }}) |
| Tagihan | {{ $payment->invoice->title }} |
| Jumlah | Rp {{ number_format($payment->invoice->amount, 0, ',', '.') }} |
</x-mail::table>

<x-mail::button :url="route('admin.payments.index')">
Verifikasi sekarang
</x-mail::button>
</x-mail::message>