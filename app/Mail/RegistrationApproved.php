<?php

namespace App\Mail;

use App\Models\Registration;
use App\Models\Student;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RegistrationApproved extends Mailable
{
    public function __construct(
        public Registration $registration,
        public Student $student,
        public bool $newAccount,
        public ?string $resetUrl = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pendaftaran diterima: '.$this->student->name);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.registration-approved');
    }
}