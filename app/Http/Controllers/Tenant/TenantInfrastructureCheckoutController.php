<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantInfrastructureCheckoutController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();

        $domains = Product::where('category', 'domain')->where('status', 'active')->orderBy('price')->get();
        $hosting = Product::where('category', 'hosting')->where('status', 'active')->orderBy('price')->get();

        return view('tenant.infrastructure.index', compact('tenant', 'domains', 'hosting'));
    }

    public function create(Product $product): View
    {
        if (!in_array($product->category, ['domain', 'hosting'])) {
            abort(404);
        }

        $tenant = app(TenantContext::class)->get();

        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();

        if (!$paymentSetting || !$paymentSetting->razorpay_key_id || !$paymentSetting->razorpay_key_secret) {
            return back()->with('error', 'Payment not configured. Please contact support.');
        }

        $amount = (int) ($product->price * 1.18 * 100);

        $rzp = new \Razorpay\Api\Api($paymentSetting->razorpay_key_id, $paymentSetting->razorpay_key_secret);
        $order = $rzp->order->create([
            'amount' => $amount,
            'currency' => 'INR',
            'receipt' => "infra_{$product->category}_{$product->id}_{$tenant->id}_" . time(),
            'payment_capture' => 1,
        ]);

        return view('tenant.infrastructure.payment', compact('tenant', 'product', 'paymentSetting', 'order'));
    }

    public function success(Request $request): View
    {
        $tenant = app(TenantContext::class)->get();
        $productId = $request->query('product_id');
        $type = $request->query('type');

        $product = Product::find($productId);

        return view('tenant.infrastructure.success', compact('product', 'type'));
    }
}