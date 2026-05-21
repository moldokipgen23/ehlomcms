<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        $subjectTpl = Setting::template('email_invoice_subject');

        return new Envelope(
            subject: Setting::renderTemplate($subjectTpl, $this->data()),
        );
    }

    public function content(): Content
    {
        $bodyTpl = Setting::template('email_invoice_body');

        return new Content(
            view: 'emails.generic',
            with: ['body' => Setting::renderTemplate($bodyTpl, $this->data())],
        );
    }

    public function attachments(): array
    {
        $this->invoice->loadMissing('client', 'items');
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $this->invoice]);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                'invoice-' . $this->invoice->invoice_number . '.pdf',
            )->withMime('application/pdf'),
        ];
    }

    /**
     * Placeholder data for template rendering.
     */
    private function data(): array
    {
        $inv = $this->invoice;
        $inv->loadMissing('client', 'items');

        $items = $inv->items->map(function ($it) {
            $qty = rtrim(rtrim(number_format((float) $it->quantity, 2), '0'), '.');

            return '- ' . $it->description . ' (x' . $qty . ') — ₹' . number_format((float) $it->total, 2);
        })->implode("\n");

        return [
            'client_name' => $inv->client->name ?? '',
            'invoice_number' => $inv->invoice_number,
            'amount' => number_format((float) $inv->total, 2),
            'due_date' => $inv->due_date?->format('M j, Y') ?? 'On receipt',
            'items' => $items !== '' ? $items : '(see attached PDF)',
        ];
    }
}
