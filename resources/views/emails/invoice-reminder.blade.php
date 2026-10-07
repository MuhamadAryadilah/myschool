<x-mail::message>
# Pengingat tagihan

Yth. {{ $parent->name }},

Tagihan berikut akan segera jatuh tempo:

<x-mail::table>
| Siswa | Tagihan | Jumlah | Jatuh tempo |
|:--|:--|--:|:--|
@foreach ($invoices as $invoice)
| {{ $invoice->student->name }} | {{ $invoice->title }} | Rp {{ number_format($invoice->amount, 0, ',', '.') }} | {{ $invoice->due_date->format('d/m/Y') }} |
@endforeach
</x-mail::table>

**Total: Rp {{ number_format($invoices->sum('amount'), 0, ',', '.') }}**

Pembayaran dapat dilakukan lalu bukti bayarnya diunggah melalui akun MySchool.

<x-mail::button :url="route('dashboard')">
Lihat tagihan
</x-mail::button>

Abaikan email ini jika pembayaran sudah dilakukan.
</x-mail::message>