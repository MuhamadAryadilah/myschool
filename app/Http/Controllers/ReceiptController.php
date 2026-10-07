<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function show(Request $request, Invoice $invoice)
    {
        $user = $request->user();

        // admin, atau orang tua pemilik anak ini
        abort_unless(
            $user->isAdmin() || $user->students()->whereKey($invoice->student_id)->exists(),
            403
        );

        // kuitansi hanya untuk tagihan yang sudah lunas
        abort_unless($invoice->isPaid(), 404);

        $invoice->load('student');

        $pdf = Pdf::loadView('receipts.invoice', [
            'invoice' => $invoice,
            'number'  => 'KWT-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
            'paidAt'  => $invoice->paid_at ?? $invoice->updated_at,
        ]);

        return $pdf->stream('kuitansi-' . $invoice->id . '.pdf');
    }
}
