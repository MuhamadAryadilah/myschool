<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class InvoiceReminder extends Mailable
{
    public function __construct(public User $parent, public Collection $invoices)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pengingat: tagihan segera jatuh tempo');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.invoice-reminder');
    }
}