<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentUploaded extends Mailable
{
    public function __construct(public Payment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Bukti bayar baru menunggu verifikasi');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.payment-uploaded');
    }
}