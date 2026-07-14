<?php

namespace App\Console\Commands;

use App\Mail\DomainExpiryWarningMail;
use App\Models\Domain;
use App\Services\MailConfigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDomainExpiryWarnings extends Command
{
    protected $signature = 'email:domain-expiry-warnings';
    protected $description = 'Send expiry warning emails for domains expiring within 30 days';

    public function handle(): int
    {
        if (!MailConfigService::configured()) {
            $this->warn('SMTP not configured. Skipping.');
            return self::SUCCESS;
        }

        $expiring = Domain::with('client')
            ->where('status', 'active')
            ->whereDate('expiry_date', '>=', now())
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($expiring as $domain) {
            if (!$domain->client?->email) {
                $skipped++;
                continue;
            }

            $daysLeft = (int) now()->startOfDay()->diffInDays($domain->expiry_date, false);

            try {
                MailConfigService::apply();
                Mail::to($domain->client->email)->send(new DomainExpiryWarningMail($domain, $daysLeft));
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Domain expiry email failed', [
                    'domain' => $domain->domain_name,
                    'error' => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        $this->info("Domain expiry warnings: {$sent} sent, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
