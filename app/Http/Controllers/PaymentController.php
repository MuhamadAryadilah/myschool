<?php

namespace App\Http\Controllers;

use App\Mail\PaymentUploaded;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function history(Request $request)
    {
        $payments = Payment::with('invoice.student')
            ->whereHas('invoice.student', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->paginate(10);

        return view('payments.history', compact('payments'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        // hanya orang tua pemilik anak ini yang boleh upload
        abort_unless(
            $request->user()->students()->whereKey($invoice->student_id)->exists(),
            403
        );

        if ($invoice->isPaid()) {
            return back()->withErrors(['proof' => 'Tagihan ini sudah lunas.']);
        }

        if ($invoice->payments()->where('status', 'menunggu')->exists()) {
            return back()->withErrors(['proof' => 'Bukti bayar sebelumnya masih menunggu verifikasi.']);
        }

        $data = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'note'  => ['nullable', 'string', 'max:500'],
        ], [
            'proof.required' => 'Foto bukti transfer belum dipilih.',
            'proof.uploaded' => 'File gagal diunggah. Kemungkinan ukurannya terlalu besar, coba foto yang lebih kecil.',
            'proof.mimes'    => 'Format file harus JPG, PNG, atau PDF.',
            'proof.max'      => 'Ukuran file terlalu besar. Maksimal 5 MB.',
        ]);

        $payment = $invoice->payments()->create([
            'user_id'    => $request->user()->id,
            'proof_path' => $request->file('proof')->store('payments', 'local'),
            'note'       => $data['note'] ?? null,
            'amount_paid' => (int) round($invoice->amount),
        ]);

        $this->notifyAdmins($payment);

        return back()->with('status', 'Bukti bayar terkirim, menunggu verifikasi admin.');
    }

    /**
     * Tampilkan bukti bayar. Hanya admin dan orang tua pemilik tagihan.
     */
    public function proof(Request $request, Payment $payment)
    {
        $user = $request->user();
        $payment->loadMissing('invoice');

        $allowed = $user->isAdmin()
            || $user->students()->whereKey($payment->invoice->student_id)->exists();

        abort_unless($allowed, 403);

        $disk = Storage::disk('local');
        abort_unless($payment->proof_path && $disk->exists($payment->proof_path), 404);

        return $disk->response($payment->proof_path);
    }

    /**
     * Beri tahu semua admin ada bukti bayar baru.
     * Kegagalan kirim email tidak boleh membatalkan upload, jadi hanya dicatat di log.
     */
    private function notifyAdmins(Payment $payment): void
    {
        $payment->load('invoice.student', 'user');

        foreach (User::where('role', 'admin')->get() as $admin) {
            try {
                Mail::to($admin)->send(new PaymentUploaded($payment));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}