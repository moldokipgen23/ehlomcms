<?php

namespace App\Console\Commands;

use App\Mail\RenewalReminderMail;
use App\Models\Activity;
use App\Models\Subscription;
use App\Services\MailConfigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email renewal reminders for subscriptions expiring within 30 days (repeats weekly)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! MailConfigService::configured()) {
            $this->warn('SMTP is not configured under Settings — renewal reminders skipped.');
            Log::warning('[reminders:send-renewals] Skipped — SMTP not configured.');

            return self::SUCCESS;
        }

        MailConfigService::apply();

        $subscriptions = Subscription::dueForRenewalReminder()
            ->with(['client', 'product'])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions due for a renewal reminder.');

            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($subscriptions as $subscription) {
            $client = $subscription->client;
            $productName = $subscription->product->name ?? 'Subscription';

            if (! $client?->email) {
                $skipped++;
                $this->line("Skipped {$client?->name} — {$productName}: no email address.");
                continue;
            }

            try {
                Mail::to($client->email)->send(new RenewalReminderMail($subscription));
            } catch (\Throwable $e) {
                $skipped++;
                $this->error("Failed for {$client->name} — {$productName}: " . $e->getMessage());
                Log::error('[reminders:send-renewals] Send failed', [
                    'subscription' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            $subscription->update(['last_reminder_sent_at' => now()]);

            Activity::create([
                'client_id' => $subscription->client_id,
                'type' => 'email',
                'title' => 'Renewal reminder emailed (automatic)',
                'description' => $productName . ' expires ' . $subscription->expiry_date?->format('M j, Y')
                    . ' — sent to ' . $client->email,
            ]);

            $sent++;
            $line = "Reminder sent to {$client->name} — {$productName} (expires "
                . $subscription->expiry_date?->format('M j, Y') . ')';
            $this->line($line);
            Log::info('[reminders:send-renewals] ' . $line);
        }

        $this->info("Done. {$sent} reminder(s) sent, {$skipped} skipped.");
        Log::info("[reminders:send-renewals] {$sent} sent, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
