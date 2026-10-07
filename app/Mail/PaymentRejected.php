<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentRejected extends Mailable
{
    public function __construct(public Payment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bukti pembayaran ditolak: ' . $this->payment->invoice->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-rejected');
    }
}