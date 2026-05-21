<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenewalReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription) {}

    public function envelope(): Envelope
    {
        $subjectTpl = Setting::template('email_renewal_subject');

        return new Envelope(
            subject: Setting::renderTemplate($subjectTpl, $this->data()),
        );
    }

    public function content(): Content
    {
        $bodyTpl = Setting::template('email_renewal_body');

        return new Content(
            view: 'emails.generic',
            with: ['body' => Setting::renderTemplate($bodyTpl, $this->data())],
        );
    }

    /**
     * Placeholder data for template rendering.
     */
    private function data(): array
    {
        $sub = $this->subscription;

        return [
            'client_name' => $sub->client->name ?? '',
            'product_name' => $sub->product->name ?? 'your service',
            'expiry_date' => $sub->expiry_date?->format('M j, Y') ?? '',
            'renewal_amount' => number_format((float) $sub->renewal_amount, 2),
        ];
    }
}
