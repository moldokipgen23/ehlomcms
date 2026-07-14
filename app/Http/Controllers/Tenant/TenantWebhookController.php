<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AddonProduct;
use App\Models\PaymentSetting;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\TenantOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TenantWebhookController extends Controller
{
    public function handleRazorpay(Request $request, string $subdomain): Response
    {
        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            return response('Tenant not found', 404);
        }

        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();

        if (!$paymentSetting) {
            return response('Payment settings not configured', 400);
        }

        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        $expectedSignature = hash_hmac('sha256', $payload, $paymentSetting->api_secret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Razorpay webhook: invalid signature', ['tenant_id' => $tenant->id]);
            return response('Invalid signature', 400);
        }

        $event = $request->input('event');
        $payloadData = $request->input('payload');

        if ($event === 'payment.captured' && $payloadData) {
            $payment = $payloadData['payment']['entity'] ?? [];

            $orderId = $payment['id'] ?? '';
            $amount = ($payment['amount'] ?? 0) / 100;
            $currency = $payment['currency'] ?? 'INR';
            $status = $payment['status'] ?? 'paid';
            $method = $payment['method'] ?? null;
            $productId = $payment['notes']['product_id'] ?? null;
            $internalOrderId = $payment['notes']['order_id'] ?? null;

            // Cart-based order (multi-item) — find by internal order ID
            if ($internalOrderId) {
                $order = TenantOrder::find($internalOrderId);
                if ($order && $order->tenant_id === $tenant->id) {
                    // Cart orders use the fulfillment lifecycle (pending →
                    // confirmed → shipped → delivered → cancelled, see
                    // TenantOrderController::STATUSES) rather than a raw
                    // payment-outcome value - a successful payment moves the
                    // order to 'confirmed' so it appears correctly in that
                    // workflow instead of landing on an unrecognized status.
                    $order->update([
                        'order_id' => $orderId,
                        'status' => $status === 'captured' ? 'confirmed' : 'failed',
                        'payment_method' => $method,
                        'customer_details' => array_merge($order->customer_details ?? [], [
                            'email' => $payment['email'] ?? null,
                            'contact' => $payment['contact'] ?? null,
                            'razorpay_payment_id' => $orderId,
                        ]),
                    ]);
                }
            } else {
                // Single-product Buy Now flow (backward compatible)
                TenantOrder::updateOrCreate(
                    ['order_id' => $orderId],
                    [
                        'tenant_id' => $tenant->id,
                        'tenant_product_id' => $productId,
                        'amount' => $amount,
                        'currency' => $currency,
                        'status' => $status === 'captured' ? 'paid' : $status,
                        'payment_method' => $method,
                        'customer_details' => [
                            'email' => $payment['email'] ?? null,
                            'contact' => $payment['contact'] ?? null,
                        ],
                    ],
                );
            }
        }

        return response('OK', 200);
    }

    public function handleAddonActivation(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        $expectedSignature = hash_hmac('sha256', $payload, config('services.razorpay.webhook_secret'));

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Addon webhook: invalid signature');
            return response('Invalid signature', 400);
        }

        $event = $request->input('event');
        $payment = $request->input('payload.payment.entity', []);

        if ($event === 'payment.captured' && $payment) {
            $notes = $payment['notes'] ?? [];
            $type = $notes['type'] ?? null;
            $addonKey = $notes['addon_key'] ?? null;
            $tenantId = $notes['tenant_id'] ?? null;

            if ($type === 'addon' && $addonKey && $tenantId) {
                $addonRecord = TenantAddon::where('tenant_id', $tenantId)
                    ->where('addon_key', $addonKey)
                    ->first();

                if ($addonRecord && $addonRecord->status !== 'active') {
                    $addonRecord->update([
                        'status' => 'active',
                        'activated_at' => now(),
                        'razorpay_payment_id' => $payment['id'],
                    ]);
                    Log::info('Add-on activated via webhook', ['tenant_id' => $tenantId, 'addon_key' => $addonKey]);
                }
            }
        }

        return response('OK', 200);
    }
}
