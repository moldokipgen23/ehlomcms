<?php

namespace App\Http\Controllers;

use App\Mail\PaymentConfirmationMail;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\EhlomBillingFulfillmentService;
use App\Services\MailConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class BillingRazorpayController extends Controller
{
    public function pay(string $portalHost, string $invoice): View
    {
        $invoice = Invoice::findOrFail($invoice);
        abort_if($invoice->status === 'draft', 404);
        abort_if($invoice->status === 'paid', 410, 'This invoice has already been paid.');

        $methods = Setting::billingPaymentMethods();
        abort_unless($methods['razorpay'] || $methods['bank'] || $methods['cash'], 503, 'Invoice payment methods are not configured yet.');

        $payment = null;
        $key = null;
        if ($methods['razorpay']) {
            [$key, $secret] = $this->credentials();
            $payment = Payment::where('invoice_id', $invoice->id)
                ->where('method', 'razorpay')
                ->where('status', 'pending')
                ->latest('id')
                ->first();

            if (!$payment) {
            $rzpOrder = (new \Razorpay\Api\Api($key, $secret))->order->create([
                'amount' => (int) round((float) $invoice->total * 100),
                'currency' => 'INR',
                'receipt' => 'invoice_' . $invoice->id . '_' . now()->timestamp,
                'notes' => [
                    'type' => 'ehlom_invoice',
                    'invoice_id' => (string) $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ],
            ]);

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total,
                'payment_date' => now()->toDateString(),
                'method' => 'razorpay',
                'reference' => $rzpOrder->id,
                'razorpay_order_id' => $rzpOrder->id,
                'status' => 'pending',
                'notes' => 'Awaiting Razorpay payment.',
            ]);
            }
        }

        return view('billing.invoices.pay', [
            'invoice' => $invoice->loadMissing('client'),
            'payment' => $payment,
            'razorpayKey' => $key,
            'billingMethods' => $methods,
            'verifyUrl' => URL::temporarySignedRoute('billing.invoices.verify', now()->addDays(30), $this->signedRouteParameters($invoice)),
        ]);
    }

    public function verify(Request $request, string $portalHost, string $invoice): JsonResponse
    {
        $invoice = Invoice::findOrFail($invoice);
        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:255'],
            'razorpay_order_id' => ['required', 'string', 'max:255'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
        ]);
        [$key, $secret] = $this->credentials();

        if (!Setting::billingPaymentMethods()['razorpay'] || !$key || !$secret || $invoice->status === 'paid') {
            return response()->json(['message' => 'This invoice is not available for online payment.'], 422);
        }

        $payment = Payment::where('invoice_id', $invoice->id)
            ->where('razorpay_order_id', $data['razorpay_order_id'])
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment order not found.'], 422);
        }

        try {
            $api = new \Razorpay\Api\Api($key, $secret);
            $api->utility->verifyPaymentSignature($data);
            $razorpayPayment = $api->payment->fetch($data['razorpay_payment_id']);
        } catch (\Throwable $e) {
            Log::warning('Ehlom invoice Razorpay verification failed', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        if (($razorpayPayment->order_id ?? null) !== $payment->razorpay_order_id
            || (int) ($razorpayPayment->amount ?? 0) !== (int) round((float) $payment->amount * 100)
            || ($razorpayPayment->currency ?? null) !== 'INR'
            || !in_array($razorpayPayment->status ?? null, ['authorized', 'captured'], true)) {
            return response()->json(['message' => 'Payment details did not match this invoice.'], 422);
        }

        $becamePaid = false;
        DB::transaction(function () use (&$becamePaid, $invoice, $payment, $data, $razorpayPayment) {
            $wasPaid = $invoice->status === 'paid';
            $payment->update([
                'status' => 'paid',
                'reference' => $data['razorpay_payment_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'notes' => 'Verified Razorpay ' . ($razorpayPayment->status ?? 'payment') . ' payment.',
            ]);

            $paid = (float) Payment::where('invoice_id', $invoice->id)->where('status', 'paid')->sum('amount');
            $newStatus = $paid >= (float) $invoice->total ? 'paid' : 'partial';
            $invoice->update(['status' => $newStatus]);
            $becamePaid = ! $wasPaid && $newStatus === 'paid';
        });
        app(EhlomBillingFulfillmentService::class)->fulfillInvoice($invoice->fresh());
        if ($becamePaid) {
            $this->sendPaymentConfirmation($invoice->fresh());
        }

        return response()->json(['redirect' => URL::temporarySignedRoute('billing.invoices.confirm', now()->addDays(30), $this->signedRouteParameters($invoice))]);
    }

    public function confirm(string $portalHost, string $invoice): View
    {
        $invoice = Invoice::findOrFail($invoice);
        return view('billing.invoices.confirm', ['invoice' => $invoice->loadMissing('client')]);
    }

    public function webhook(Request $request): Response
    {
        $secret = Setting::getEncrypted('billing_razorpay_webhook_secret');
        if (!$secret) {
            return response('Webhook secret not configured', 400);
        }

        $payload = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature');
        if (!hash_equals(hash_hmac('sha256', $payload, $secret), $signature)) {
            return response('Invalid signature', 400);
        }

        if ($request->input('event') === 'payment.captured') {
            $entity = $request->input('payload.payment.entity', []);
            $payment = Payment::where('razorpay_order_id', $entity['order_id'] ?? null)->where('status', 'pending')->first();
            if ($payment && (int) ($entity['amount'] ?? 0) === (int) round((float) $payment->amount * 100)) {
                $paidInvoice = null;
                $becamePaid = false;
                DB::transaction(function () use (&$paidInvoice, &$becamePaid, $payment, $entity) {
                    $payment->update([
                        'status' => 'paid',
                        'reference' => $entity['id'] ?? $payment->reference,
                        'razorpay_payment_id' => $entity['id'] ?? null,
                        'notes' => 'Confirmed by Razorpay webhook.',
                    ]);
                    $paidInvoice = $payment->invoice;
                    if ($paidInvoice) {
                        $wasPaid = $paidInvoice->status === 'paid';
                        $paid = (float) Payment::where('invoice_id', $paidInvoice->id)->where('status', 'paid')->sum('amount');
                        $newStatus = $paid >= (float) $paidInvoice->total ? 'paid' : 'partial';
                        $paidInvoice->update(['status' => $newStatus]);
                        $becamePaid = ! $wasPaid && $newStatus === 'paid';
                    }
                });
                if ($paidInvoice) {
                    $paidInvoice = $paidInvoice->fresh();
                    app(EhlomBillingFulfillmentService::class)->fulfillInvoice($paidInvoice);
                    if ($becamePaid) {
                        $this->sendPaymentConfirmation($paidInvoice);
                    }
                }
            }
        }

        return response('OK');
    }

    private function credentials(): array
    {
        return [Setting::getEncrypted('billing_razorpay_key'), Setting::getEncrypted('billing_razorpay_secret')];
    }

    private function signedRouteParameters(Invoice $invoice): array
    {
        return [
            'portalHost' => parse_url(config('app.url'), PHP_URL_HOST) ?: 'portal.ehlom.com',
            'invoice' => $invoice,
        ];
    }

    private function sendPaymentConfirmation(Invoice $invoice): void
    {
        if (! $invoice->client?->email || ! MailConfigService::configured()) {
            Log::warning('Ehlom payment confirmation skipped', [
                'invoice_id' => $invoice->id,
                'reason' => $invoice->client?->email ? 'mail_not_configured' : 'client_email_missing',
            ]);

            return;
        }

        try {
            MailConfigService::apply();
            \Illuminate\Support\Facades\Mail::to($invoice->client->email)->send(new PaymentConfirmationMail($invoice->loadMissing('client')));
        } catch (\Throwable $e) {
            Log::error('Ehlom payment confirmation email failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
