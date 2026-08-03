<?php

namespace App\Console\Commands;

use App\Models\ExternalIntegration;
use App\Services\ExternalIntegrations\ExternalIntegrationManager;
use Illuminate\Console\Command;

class SyncExternalIntegrations extends Command
{
    protected $signature = 'integrations:sync {integration? : Optional integration ID}';
    protected $description = 'Synchronize external ERP catalogs, accounts, subscriptions, and invoices';

    public function handle(ExternalIntegrationManager $manager): int
    {
        $query = ExternalIntegration::where('status', 'active');
        if ($this->argument('integration')) $query->whereKey($this->argument('integration'));
        $integrations = $query->get();
        foreach ($integrations as $integration) {
            try {
                $result = $manager->sync($integration);
                $this->info("{$integration->name}: " . json_encode($result));
            } catch (\Throwable $e) {
                $this->error("{$integration->name}: {$e->getMessage()}");
            }
        }
        return self::SUCCESS;
    }
}
