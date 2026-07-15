<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\TenantAddon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Verified against the agency's own platform Razorpay secret
 * (Setting::platform_razorpay_key_secret) - add-on purchases are charged to
 * the agency's account, not any individual tenant's, so there is no
 * per-tenant secret to check here.
 */
class TenantAddonWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        $secret = Setting::get('platform_razorpay_key_secret');

        if (!$secret) {
            return response()->json(['error' => 'Payment config missing'], 400);
        }

        if (!$this->verifySignature($payload, $signature, $secret)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);

        if (($event['event'] ?? null) === 'payment.captured') {
            $this->activateAddon($event['payload']['payment']['entity'] ?? []);
        }

        return response()->json(['status' => 'ok']);
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

        if ($existing) {
            $existing->update(['status' => 'active', 'activated_at' => now()]);
        } else {
            TenantAddon::create([
                'tenant_id' => $tenantId,
                'addon_key' => $addonKey,
                'status' => 'active',
                'activated_at' => now(),
            ]);
        }
    }
}
