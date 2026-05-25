<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProjectCompletionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project) {}

    public function envelope(): Envelope
    {
        $tpl = Setting::template('email_completion_subject');

        return new Envelope(
            subject: Setting::renderTemplate($tpl, $this->data()),
        );
    }

    public function content(): Content
    {
        $tpl = Setting::template('email_completion_body');

        return new Content(
            view: 'emails.generic',
            with: ['body' => Setting::renderTemplate($tpl, $this->data())],
        );
    }

    /**
     * Attach the auto-generated summary PDF plus the optional uploaded
     * deliverable PDF (handover doc, designs, final spec, etc.).
     */
    public function attachments(): array
    {
        $this->project->loadMissing('client', 'products', 'invoice', 'subscriptions.product');

        $summaryPdf = Pdf::loadView('pdf.project-summary', ['project' => $this->project]);

        $attachments = [
            Attachment::fromData(
                fn () => $summaryPdf->output(),
                'project-summary-' . $this->safeSlug($this->project->title) . '.pdf',
            )->withMime('application/pdf'),
        ];

        if ($this->project->deliverable_pdf && Storage::disk('public')->exists($this->project->deliverable_pdf)) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->project->deliverable_pdf)
                ->as('deliverable-' . $this->safeSlug($this->project->title) . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }

    private function data(): array
    {
        $p = $this->project;

        return [
            'client_name' => $p->client->name ?? '',
            'project_title' => $p->title,
            'delivery_date' => $p->delivery_date?->format('M j, Y') ?? '',
            'completed_at' => $p->completed_at?->format('M j, Y') ?? now()->format('M j, Y'),
        ];
    }

    private function safeSlug(string $value): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $value) ?? '';

        return trim(strtolower($slug), '-') ?: 'project';
    }
}
