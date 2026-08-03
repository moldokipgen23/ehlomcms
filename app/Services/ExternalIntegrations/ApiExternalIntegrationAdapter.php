<?php

namespace App\Services\ExternalIntegrations;

use App\Contracts\ExternalIntegrationAdapter;
use App\Models\Client;
use App\Models\ExternalAccount;
use App\Models\ExternalCatalogProduct;
use App\Models\ExternalIntegration;
use App\Models\ExternalInvoice;
use App\Models\ExternalSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ApiExternalIntegrationAdapter implements ExternalIntegrationAdapter
{
    public function sync(ExternalIntegration $integration): array
    {
        $result = ['catalog' => 0, 'accounts' => 0, 'subscriptions' => 0, 'invoices' => 0];

        foreach ($this->collection($integration, 'catalog') as $row) {
            $id = (string) ($row['id'] ?? $row['external_id'] ?? '');
            if ($id === '') continue;
            ExternalCatalogProduct::updateOrCreate(
                ['external_integration_id' => $integration->id, 'external_id' => $id],
                [
                    'external_type' => $row['type'] ?? null,
                    'name' => $row['name'] ?? 'Unnamed plan',
                    'description' => $row['description'] ?? null,
                    'category' => $row['category'] ?? $integration->driver,
                    'billing_cycle' => $row['billing_cycle'] ?? $row['cycle'] ?? null,
                    'price' => $row['price'] ?? $row['amount'] ?? 0,
                    'currency' => strtoupper($row['currency'] ?? 'INR'),
                    'status' => $this->status($row['status'] ?? null),
                    'metadata' => $row,
                    'last_synced_at' => now(),
                ]
            );
            $result['catalog']++;
        }

        foreach ($this->collection($integration, 'accounts') as $row) {
            $id = (string) ($row['id'] ?? $row['external_id'] ?? '');
            if ($id === '') continue;
            $account = ExternalAccount::updateOrCreate(
                ['external_integration_id' => $integration->id, 'external_id' => $id],
                [
                    'external_type' => $row['type'] ?? 'account',
                    'name' => $row['name'] ?? $row['business_name'] ?? null,
                    'email' => $row['email'] ?? $row['admin_email'] ?? null,
                    'phone' => $row['phone'] ?? $row['support_phone'] ?? null,
                    'metadata' => $row,
                    'last_synced_at' => now(),
                ]
            );
            $this->linkAccount($account);
            $result['accounts']++;
        }

        foreach ($this->collection($integration, 'subscriptions') as $row) {
            $id = (string) ($row['id'] ?? $row['external_id'] ?? '');
            if ($id === '') continue;
            $account = $this->account($integration, $row['account_id'] ?? $row['school_id'] ?? $row['customer_id'] ?? null);
            $subscription = ExternalSubscription::updateOrCreate(
                ['external_integration_id' => $integration->id, 'external_id' => $id],
                [
                    'external_account_id' => $account?->id,
                    'client_id' => $account?->client_id,
                    'tenant_id' => $account?->tenant_id,
                    'external_product_id' => isset($row['product_id']) ? (string) $row['product_id'] : ($row['package_id'] ?? null),
                    'product_name' => $row['product_name'] ?? $row['package_name'] ?? $row['name'] ?? 'External subscription',
                    'status' => $this->status($row['status'] ?? null),
                    'billing_cycle' => $row['billing_cycle'] ?? $row['cycle'] ?? null,
                    'amount' => $row['amount'] ?? $row['price'] ?? $row['renewal_amount'] ?? 0,
                    'currency' => strtoupper($row['currency'] ?? 'INR'),
                    'starts_at' => $row['starts_at'] ?? $row['start_date'] ?? null,
                    'ends_at' => $row['ends_at'] ?? $row['end_date'] ?? null,
                    'renews_at' => $row['renews_at'] ?? $row['renewal_date'] ?? null,
                    'metadata' => $row,
                    'last_synced_at' => now(),
                ]
            );
            $result['subscriptions']++;
        }

        foreach ($this->collection($integration, 'invoices') as $row) {
            $id = (string) ($row['id'] ?? $row['external_id'] ?? '');
            if ($id === '') continue;
            $account = $this->account($integration, $row['account_id'] ?? $row['school_id'] ?? $row['customer_id'] ?? null);
            $subscription = $this->subscription($integration, $row['subscription_id'] ?? null);
            ExternalInvoice::updateOrCreate(
                ['external_integration_id' => $integration->id, 'external_id' => $id],
                [
                    'external_account_id' => $account?->id,
                    'external_subscription_id' => $subscription?->id,
                    'client_id' => $account?->client_id,
                    'invoice_number' => $row['invoice_number'] ?? $row['number'] ?? null,
                    'status' => $this->status($row['status'] ?? null),
                    'amount' => $row['amount'] ?? $row['total'] ?? 0,
                    'currency' => strtoupper($row['currency'] ?? 'INR'),
                    'issued_at' => $row['issued_at'] ?? $row['issue_date'] ?? null,
                    'due_at' => $row['due_at'] ?? $row['due_date'] ?? null,
                    'paid_at' => $row['paid_at'] ?? null,
                    'metadata' => $row,
                    'last_synced_at' => now(),
                ]
            );
            $result['invoices']++;
        }

        return $result;
    }

    public function handleWebhook(ExternalIntegration $integration, array $payload): void
    {
        $type = (string) ($payload['type'] ?? $payload['event'] ?? 'external.updated');
        // Webhooks are deliberately followed by a bounded sync. This keeps the
        // external ERP authoritative and makes duplicate webhook deliveries safe.
        $this->sync($integration);
        Log::info('External integration webhook synchronized', ['integration' => $integration->id, 'type' => $type]);
    }

    private function collection(ExternalIntegration $integration, string $resource): array
    {
        $path = data_get($integration->settings, "paths.$resource", "api/v1/integrations/$resource");
        $response = $this->request($integration)->get(rtrim($integration->base_url, '/') . '/' . ltrim($path, '/'));
        if ($response->failed()) {
            throw new RuntimeException("{$resource} endpoint returned HTTP {$response->status()}.");
        }

        $payload = $response->json();
        $rows = data_get($payload, 'data', $payload);
        if (isset($rows['items']) && is_array($rows['items'])) $rows = $rows['items'];
        if (!is_array($rows)) throw new RuntimeException("{$resource} endpoint did not return a list.");
        return array_values($rows);
    }

    private function request(ExternalIntegration $integration)
    {
        $credentials = $integration->credentials ?: [];
        $request = Http::acceptJson()->timeout(20)->retry(2, 250);
        if (!empty($credentials['api_key'])) {
            $request = $request->withHeaders(['X-Integration-Key' => $credentials['api_key']]);
        }
        if (!empty($credentials['bearer_token'])) {
            $request = $request->withToken($credentials['bearer_token']);
        }
        return $request;
    }

    private function account(ExternalIntegration $integration, mixed $externalId): ?ExternalAccount
    {
        return $externalId === null ? null : ExternalAccount::where('external_integration_id', $integration->id)->where('external_id', (string) $externalId)->first();
    }

    private function subscription(ExternalIntegration $integration, mixed $externalId): ?ExternalSubscription
    {
        return $externalId === null ? null : ExternalSubscription::where('external_integration_id', $integration->id)->where('external_id', (string) $externalId)->first();
    }

    private function linkAccount(ExternalAccount $account): void
    {
        $client = null;
        if ($account->email) $client = Client::where('email', $account->email)->first();
        if (!$client && $account->phone) $client = Client::where('phone', $account->phone)->orWhere('whatsapp', $account->phone)->first();
        if ($client && $account->client_id !== $client->id) $account->update(['client_id' => $client->id]);
    }

    private function status(?string $status): string
    {
        $value = Str::lower(trim((string) $status));
        return match (true) {
            in_array($value, ['active', 'succeed', 'success', 'current cycle']) => 'active',
            $value === 'paid' => 'paid',
            in_array($value, ['cancelled', 'canceled', 'inactive']) => 'cancelled',
            in_array($value, ['expired', 'overdue', 'over due']) => 'expired',
            default => $value !== '' ? $value : 'pending',
        };
    }
}
