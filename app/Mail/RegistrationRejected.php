<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RegistrationRejected extends Mailable
{
    public function __construct(public Registration $registration)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pendaftaran belum dapat diterima');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.registration-rejected');
    }
}