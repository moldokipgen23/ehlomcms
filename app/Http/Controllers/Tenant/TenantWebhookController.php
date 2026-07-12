<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\Tenant;
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

        return response('OK', 200);
    }
}
