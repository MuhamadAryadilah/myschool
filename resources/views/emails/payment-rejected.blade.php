<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti pembayaran ditolak</title>
</head>
<body style="margin:0;padding:24px;background:#f3efe8;font-family:Arial,Helvetica,sans-serif;color:#231f1a;">
    <div style="max-width:520px;margin:0 auto;background:#ffffff;border:1px solid #e7e1d6;border-radius:8px;padding:28px;">
        <p style="margin:0 0 16px;font-size:18px;font-weight:bold;color:#294e3b;">MySchool</p>

        <h2 style="margin:0 0 12px;font-size:20px;">Bukti pembayaran ditolak</h2>

        <p style="margin:0 0 12px;">Halo, {{ $payment->user->name }}.</p>
        <p style="margin:0 0 16px;">
            Bukti pembayaran untuk tagihan
            <strong>{{ $payment->invoice->title }}</strong>
            atas nama {{ $payment->invoice->student->name }}
            (Rp {{ number_format($payment->invoice->amount, 0, ',', '.') }}) belum dapat kami terima.
        </p>

        <div style="margin:0 0 20px;padding:12px 14px;background:#f8e4da;border-left:4px solid #c2562f;border-radius:4px;">
            <div style="font-size:12px;color:#7e7667;margin-bottom:4px;">Alasan dari admin</div>
            <div>{{ $payment->admin_note }}</div>
        </div>

        <p style="margin:0 0 20px;">Silakan upload ulang bukti pembayaran yang sesuai melalui dashboard.</p>

        <p style="margin:0 0 20px;">
            <a href="{{ route('dashboard') }}"
               style="display:inline-block;padding:10px 18px;background:#326249;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;">
                Buka dashboard
            </a>
        </p>

        <p style="margin:0;font-size:12px;color:#7e7667;">Email ini dikirim otomatis oleh MySchool.</p>
    </div>
</body>
</html>