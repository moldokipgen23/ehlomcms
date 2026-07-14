<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OnboardingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Tenant $tenant) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Complete Your Site Setup — {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.onboarding-reminder',
            with: $this->data(),
        );
    }

    private function data(): array
    {
        $tenant = $this->tenant;
        $step = $tenant->onboarding_step;
        $steps = [
            1 => 'Basic Info',
            2 => 'Choose Theme',
            3 => 'Select Modules',
            4 => 'Setup Domain',
            5 => 'Done',
        ];

        return [
            'client_name' => $tenant->client->name ?? 'there',
            'tenant_name' => $tenant->name,
            'current_step' => $step,
            'current_step_name' => $steps[$step] ?? 'Unknown',
            'next_step' => min($step + 1, 5),
            'next_step_name' => $steps[min($step + 1, 5)] ?? 'Done',
            'login_url' => config('app.url') . '/admin/tenants/' . $tenant->id . '/onboarding',
        ];
    }
}
