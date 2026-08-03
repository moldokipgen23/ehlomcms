<?php

namespace App\Services\ExternalIntegrations;

use App\Contracts\ExternalIntegrationAdapter;
use App\Models\ExternalIntegration;
use RuntimeException;

class ExternalIntegrationManager
{
    public function adapter(ExternalIntegration $integration): ExternalIntegrationAdapter
    {
        return match ($integration->driver) {
            'eschool', 'generic_api' => app(ApiExternalIntegrationAdapter::class),
            default => throw new RuntimeException("Unsupported integration driver: {$integration->driver}"),
        };
    }

    public function sync(ExternalIntegration $integration): array
    {
        $integration->update(['last_sync_status' => 'running', 'last_error' => null]);
        try {
            $result = $this->adapter($integration)->sync($integration);
            $integration->update(['last_synced_at' => now(), 'last_sync_status' => 'success']);
            return $result;
        } catch (\Throwable $e) {
            $integration->update(['last_sync_status' => 'failed', 'last_error' => $e->getMessage()]);
            throw $e;
        }
    }
}
