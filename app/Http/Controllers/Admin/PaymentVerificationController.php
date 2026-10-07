<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentApproved;
use App\Mail\PaymentRejected;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentVerificationController extends Controller
{
    public function index()
    {
        $payments = Payment::with('invoice.student', 'user')
            ->orderByRaw("status = 'menunggu' desc")
            ->latest()
            ->get();

        return view('admin.payments.index', compact('payments'));
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $status = $request->query('status');
        $filename = 'pembayaran-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($status) {
            $out = fopen('php://output', 'w');

            // Penanda UTF-8 supaya Excel membaca karakter dengan benar
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Tanggal Upload', 'NIS', 'Siswa', 'Kelas', 'Tagihan', 'Jumlah', 'Status', 'Diunggah Oleh', 'Catatan Orang Tua', 'Catatan Admin', 'Tanggal Verifikasi'], ';', '"', '');

            Payment::query()
                ->with('invoice.student', 'user')
                ->when(in_array($status, ['menunggu', 'diterima', 'ditolak'], true), fn ($q) => $q->where('status', $status))
                ->chunkById(500, function ($payments) use ($out) {
                    foreach ($payments as $payment) {
                        $label = match ($payment->status) {
                            'menunggu' => 'Menunggu verifikasi',
                            'diterima' => 'Diterima',
                            default => 'Ditolak',
                        };

                        fputcsv($out, [
                            $payment->created_at?->format('Y-m-d H:i') ?? '',
                            \App\Support\CsvCell::safe($payment->invoice?->student?->nis),
                            \App\Support\CsvCell::safe($payment->invoice?->student?->name),
                            \App\Support\CsvCell::safe($payment->invoice?->student?->kelas),
                            \App\Support\CsvCell::safe($payment->invoice?->title),
                            $payment->invoice?->amount ?? '',
                            $label,
                            \App\Support\CsvCell::safe($payment->user?->name),
                            \App\Support\CsvCell::safe($payment->note),
                            \App\Support\CsvCell::safe($payment->admin_note),
                            $payment->verified_at?->format('Y-m-d H:i') ?? '',
                        ], ';', '"', '');
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function approve(Payment $payment)
    {
        if ($payment->status !== 'menunggu') {
            return back()->withErrors(['payment' => 'Pembayaran ini sudah diproses.']);
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status'      => 'diterima',
                'verified_at' => now(),
            ]);

            $payment->invoice->update([
                'status'  => 'lunas',
                'paid_at' => now(),
            ]);
        });

        $payment->load('invoice.student', 'user');
        $this->notify($payment, new PaymentApproved($payment));

        return back()->with('status', 'Pembayaran diterima, tagihan ditandai lunas.');
    }

    public function reject(Request $request, Payment $payment)
    {
        if ($payment->status !== 'menunggu') {
            return back()->withErrors(['payment' => 'Pembayaran ini sudah diproses.']);
        }

        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:255'],
        ]);

        $payment->update([
            'status'      => 'ditolak',
            'admin_note'  => $data['admin_note'],
            'verified_at' => now(),
        ]);

        $payment->load('invoice.student', 'user');
        $this->notify($payment, new PaymentRejected($payment));

        return back()->with('status', 'Pembayaran ditolak, orang tua bisa upload ulang.');
    }

    /**
     * Kirim email ke orang tua yang mengupload bukti.
     * Kegagalan kirim email tidak boleh membatalkan verifikasi, jadi hanya dicatat di log.
     */
    private function notify(Payment $payment, Mailable $mail): void
    {
        if (! $payment->user) {
            return;
        }

        try {
            Mail::to($payment->user)->send($mail);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}