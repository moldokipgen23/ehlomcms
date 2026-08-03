<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Carbon;

class ClientServiceLedger
{
    public function assignProduct(Client $client, Product $product, ?float $price = null, ?Carbon $start = null): void
    {
        $amount = $price ?? (float) $product->price;

        $client->products()->syncWithoutDetaching([
            $product->id => ['custom_price' => $price],
        ]);

        if (! $product->isRecurring()) {
            return;
        }

        $startDate = ($start ?? now())->copy()->startOfDay();
        $expiryDate = $this->expiryFromCycle($startDate, $product->billing_cycle);

        Subscription::updateOrCreate(
            [
                'client_id' => $client->id,
                'product_id' => $product->id,
                'project_id' => null,
            ],
            [
                'start_date' => $startDate->toDateString(),
                'expiry_date' => $expiryDate->toDateString(),
                'renewal_amount' => $amount,
                'status' => 'active',
                'auto_invoice' => true,
                'notes' => 'Auto-linked from assigned/purchased SaaS product.',
            ]
        );
    }

    public function syncTenantHosting(Tenant $tenant): void
    {
        $tenant->loadMissing('client', 'hostingPlan');

        if (! $tenant->client || ! $tenant->hostingPlan) {
            return;
        }

        $this->assignProduct($tenant->client, $tenant->hostingPlan);

        Subscription::where('client_id', $tenant->client->id)
            ->whereHas('product', fn ($query) => $query->where('category', 'hosting'))
            ->where('product_id', '!=', $tenant->hostingPlan->id)
            ->where('status', 'active')
            ->update([
                'status' => 'cancelled',
                'notes' => 'Cancelled automatically because tenant hosting plan changed.',
            ]);
    }

    private function expiryFromCycle(Carbon $startDate, string $cycle): Carbon
    {
        return match ($cycle) {
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            default => $startDate->copy()->addYear(),
        };
    }
}
