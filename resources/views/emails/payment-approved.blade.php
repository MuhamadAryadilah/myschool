<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pembayaran diterima</title>
</head>
<body style="margin:0;padding:24px;background:#f3efe8;font-family:Arial,Helvetica,sans-serif;color:#231f1a;">
    <div style="max-width:520px;margin:0 auto;background:#ffffff;border:1px solid #e7e1d6;border-radius:8px;padding:28px;">
        <p style="margin:0 0 16px;font-size:18px;font-weight:bold;color:#294e3b;">MySchool</p>

        <h2 style="margin:0 0 12px;font-size:20px;">Pembayaran diterima</h2>

        <p style="margin:0 0 12px;">Halo, {{ $payment->user->name }}.</p>
        <p style="margin:0 0 16px;">Bukti pembayaran Anda sudah diverifikasi dan tagihan berikut dinyatakan lunas.</p>

        <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
            <tr>
                <td style="padding:6px 0;color:#7e7667;width:38%;">Siswa</td>
                <td style="padding:6px 0;">{{ $payment->invoice->student->name }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#7e7667;">Tagihan</td>
                <td style="padding:6px 0;">{{ $payment->invoice->title }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#7e7667;">Jumlah</td>
                <td style="padding:6px 0;font-weight:bold;">Rp {{ number_format($payment->invoice->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#7e7667;">Tanggal verifikasi</td>
                <td style="padding:6px 0;">{{ ($payment->verified_at ?? now())->format('d/m/Y') }}</td>
            </tr>
        </table>

        <p style="margin:0 0 20px;">
            <a href="{{ route('receipts.show', $payment->invoice) }}"
               style="display:inline-block;padding:10px 18px;background:#326249;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;">
                Unduh kuitansi
            </a>
        </p>

        <p style="margin:0;font-size:12px;color:#7e7667;">Email ini dikirim otomatis oleh MySchool. Anda perlu login untuk mengunduh kuitansi.</p>
    </div>
</body>
</html>