<?php

namespace App\Mail;

use App\Models\Domain;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DomainExpiryWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Domain $domain, public int $daysLeft) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Domain Expiring Soon: {$this->domain->domain_name} ({$this->daysLeft} days)",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.domain-expiry-warning',
            with: $this->data(),
        );
    }

    private function data(): array
    {
        $domain = $this->domain;

        return [
            'client_name' => $domain->client->name ?? 'Customer',
            'domain_name' => $domain->domain_name,
            'expiry_date' => $domain->expiry_date?->format('M j, Y') ?? 'Unknown',
            'days_left' => $this->daysLeft,
            'renewal_cost' => $domain->renewal_cost ? '₹' . number_format($domain->renewal_cost, 0) : 'Contact us',
            'registrar' => $domain->registrar ?? 'Unknown',
        ];
    }
}
