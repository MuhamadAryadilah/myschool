<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kuitansi {{ $number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #222; }
        .wrap { border: 2px solid #1a8f3c; }
        .head { background: #1a8f3c; color: #fff; padding: 18px 28px; text-align: center; }
        h1 { margin: 0 0 4px; font-size: 22px; letter-spacing: 3px; }
        .sub { color: #e6f4ea; }
        .body { padding: 24px 28px 28px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 9px 4px; vertical-align: top; border-bottom: 1px solid #e5e5e5; }
        td.label { width: 32%; color: #555; }
        .amount { margin: 24px 0; padding: 16px; background: #eaf6ee; border: 1px solid #1a8f3c; font-size: 22px; font-weight: bold; text-align: center; color: #14532d; }
        .stamp { color: #1a8f3c; border: 2px solid #1a8f3c; display: inline-block; padding: 4px 14px; font-weight: bold; }
        .footer { margin-top: 30px; text-align: right; }
        .footer p { color: #666; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="head">
            <h1>KUITANSI</h1>
            <div class="sub">MySchool &middot; No. {{ $number }}</div>
        </div>

        <div class="body">
            <table>
                <tr>
                    <td class="label">Telah diterima untuk</td>
                    <td>: {{ $invoice->title }}</td>
                </tr>
                <tr>
                    <td class="label">Nama siswa</td>
                    <td>: {{ $invoice->student->name }}</td>
                </tr>
                <tr>
                    <td class="label">NIS / Kelas</td>
                    <td>: {{ $invoice->student->nis }} / {{ $invoice->student->kelas }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal pembayaran</td>
                    <td>: {{ $paidAt->format('d/m/Y') }}</td>
                </tr>
            </table>

            <div class="amount">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</div>

            <div class="footer">
                <span class="stamp">LUNAS</span>
                <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</body>
</html>