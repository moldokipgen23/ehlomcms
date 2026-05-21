<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Ehlom OS — SMTP Test Email');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generic',
            with: ['body' => "This is a test email from Ehlom OS.\n\nIf you received this, your SMTP settings are working correctly."],
        );
    }
}
