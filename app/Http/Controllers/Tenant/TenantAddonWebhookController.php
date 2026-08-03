<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AddonProduct;
use App\Models\PaymentSetting;
use App\Models\TenantAddon;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TenantAddonWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        $tenantId = $this->extractTenantId($payload);
        if (!$tenantId) {
            return response()->json(['error' => 'Tenant not found'], 400);
        }

        $paymentSetting = PaymentSetting::where('tenant_id', $tenantId)->first();
        if (!$paymentSetting || !$paymentSetting->razorpay_key_secret) {
            return response()->json(['error' => 'Payment config missing'], 400);
        }

        if (!$this->verifySignature($payload, $signature, $paymentSetting->razorpay_key_secret)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);

        if ($event['event'] === 'payment.captured') {
            $this->activateAddon($event['payload']['payment']['entity']);
        }

        return response()->json(['status' => 'ok']);
    }

    private function extractTenantId(string $payload): ?int
    {
        $data = json_decode($payload, true);
        if (!isset($data['payload']['payment']['entity']['notes']['tenant_id'])) {
            return null;
        }
        return (int) $data['payload']['payment']['entity']['notes']['tenant_id'];
    }

    private function verifySignature(string $payload, ?string $signature, string $secret): bool
    {
        if (!$signature) {
            return false;
        }
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    private function activateAddon(array $payment): void
    {
        $tenantId = (int) ($payment['notes']['tenant_id'] ?? 0);
        $addonKey = $payment['notes']['addon_key'] ?? null;

        if (!$tenantId || !$addonKey) {
            return;
        }

        $existing = TenantAddon::where('tenant_id', $tenantId)
            ->where('addon_key', $addonKey)
            ->first();

        $addonProduct = AddonProduct::where('key', $addonKey)->first();

        if (! $existing) {
            $existing = TenantAddon::create([
                'tenant_id' => $tenantId,
                'addon_key' => $addonKey,
            ]);
        }

        $this->activateRecord($existing, $addonProduct);
    }

    private function activateRecord(TenantAddon $addon, ?AddonProduct $addonProduct): void
    {
        $activatedAt = now();
        $cycle = $addonProduct?->billing_cycle ?? 'monthly';

        $addon->update([
            'status' => 'active',
            'activated_at' => $activatedAt,
            'expires_at' => $cycle === 'one_time' ? null : $this->expiryFromCycle($activatedAt, $cycle),
            'renewal_amount' => $addonProduct ? (float) $addonProduct->price : null,
            'billing_cycle' => $cycle,
            'auto_invoice' => $cycle !== 'one_time',
        ]);
    }

    private function expiryFromCycle(Carbon $start, string $cycle): Carbon
    {
        return match ($cycle) {
            'quarterly' => $start->copy()->addMonths(3),
            'yearly' => $start->copy()->addYear(),
            default => $start->copy()->addMonth(),
        };
    }
}
