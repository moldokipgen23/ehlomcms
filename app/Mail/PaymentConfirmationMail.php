<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: Setting::renderTemplate(Setting::template('email_payment_subject'), $this->data()),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generic',
            with: ['body' => Setting::renderTemplate(Setting::template('email_payment_body'), $this->data())],
        );
    }

    /**
     * Placeholder data for template rendering.
     */
    private function data(): array
    {
        $inv = $this->invoice;
        $inv->loadMissing('client');

        return [
            'client_name' => $inv->client->name ?? '',
            'invoice_number' => $inv->invoice_number,
            'amount' => number_format((float) $inv->total, 2),
            'payment_date' => now()->format('M j, Y'),
        ];
    }
}
