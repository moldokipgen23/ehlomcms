<?php

namespace App\Contracts;

use App\Models\ExternalIntegration;

interface ExternalIntegrationAdapter
{
    /** @return array{catalog:int,accounts:int,subscriptions:int,invoices:int} */
    public function sync(ExternalIntegration $integration): array;

    public function handleWebhook(ExternalIntegration $integration, array $payload): void;
}
