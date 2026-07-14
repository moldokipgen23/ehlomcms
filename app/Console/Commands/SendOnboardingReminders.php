<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\MailConfigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOnboardingReminders extends Command
{
    protected $signature = 'email:onboarding-reminders';
    protected $description = 'Remind tenants with incomplete onboarding to finish setup';

    public function handle(): int
    {
        if (!MailConfigService::configured()) {
            $this->warn('SMTP not configured. Skipping.');
            return self::SUCCESS;
        }

        $incomplete = Tenant::where('status', 'active')
            ->where('onboarding_step', '>', 0)
            ->where('onboarding_step', '<', 5)
            ->with('client')
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($incomplete as $tenant) {
            if (!$tenant->client?->email) {
                $skipped++;
                continue;
            }

            try {
                MailConfigService::apply();
                Mail::to($tenant->client->email)->send(
                    new \App\Mail\OnboardingReminderMail($tenant)
                );
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Onboarding reminder failed', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        $this->info("Onboarding reminders: {$sent} sent, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
