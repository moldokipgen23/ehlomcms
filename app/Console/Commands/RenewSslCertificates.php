<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class RenewSslCertificates extends Command
{
    protected $signature = 'ssl:renew-all';
    protected $description = 'Renew SSL certificates for all verified custom domains nearing expiry';

    public function handle(): int
    {
        $tenants = Tenant::where('domain_status', 'verified')
            ->whereNotNull('custom_domain')
            ->get();

        if ($tenants->isEmpty()) {
            $this->info('No verified custom domains found.');
            return self::SUCCESS;
        }

        $renewed = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            $domain = $tenant->custom_domain;
            $this->info("Checking SSL for {$domain}...");

            $output = [];
            $exitCode = 0;

            exec("certbot certificates -d " . escapeshellarg($domain) . " 2>&1", $output, $exitCode);

            $outputStr = implode("\n", $output);

            if (str_contains($outputStr, 'INVALID') || str_contains($outputStr, 'EXPIRED') || str_contains($outputStr, '30 days')) {
                $this->info("  Certificate needs renewal for {$domain}");

                exec("certbot renew --cert-name " . escapeshellarg($domain) . " --non-interactive --agree-tos --deploy-hook 'nginx -s reload' 2>&1", $renewOutput, $renewExit);

                if ($renewExit === 0) {
                    $this->info("  ✓ Renewed successfully");
                    $renewed++;
                } else {
                    $this->error("  ✗ Renewal failed: " . implode("\n", $renewOutput));
                    $failed++;
                }
            } else {
                $this->info("  Certificate is still valid for {$domain}");
            }
        }

        $this->info("\nDone. Renewed: {$renewed}, Failed: {$failed}");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
