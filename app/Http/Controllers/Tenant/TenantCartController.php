<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\TenantAbandonedCart;
use App\Models\TenantCoupon;
use App\Models\TenantFeatureSetting;
use App\Models\TenantLoyaltyTransaction;
use App\Models\TenantOrder;
use App\Models\TenantProduct;
use App\Models\TenantProductVariant;
use App\Models\TenantShippingRule;
use App\Models\Theme;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class TenantCartController extends Controller
{
    private const CART_KEY = 'tenant_cart_';

    private function requireModule(string $key): void
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant || !$tenant->hasModule($key), 404);
    }

    private function cartKey(): string
    {
        $tenant = app(TenantContext::class)->get();
        return self::CART_KEY . ($tenant ? $tenant->id : 'guest');
    }

    private function getCart(): array
    {
        return session($this->cartKey(), []);
    }

    private function saveCart(array $cart): void
    {
        session([$this->cartKey() => $cart]);
    }

    private function cartCount(): int
    {
        return array_sum(array_column($this->getCart(), 'quantity'));
    }

    private function cartSubtotal(array $cart): float
    {
        $tenant = app(TenantContext::class)->get();
        $products = TenantProduct::where('tenant_id', $tenant->id)
            ->whereIn('id', $this->cartProductIds($cart))
            ->with(['variants'])
            ->get()
            ->keyBy('id');

        $total = 0;
        foreach ($cart as $item) {
            $product = $products->get($item['product_id'] ?? null);
            if (!$product) {
                continue;
            }
            $variant = !empty($item['variant_id']) ? $product->variants->firstWhere('id', $item['variant_id']) : null;
            $total += ($variant ? (float) $variant->effective_price : (float) $product->price) * max(1, (int) ($item['quantity'] ?? 1));
        }

        return $total;
    }

    private function rememberAbandonedCart(array $cart, array $contact = []): void
    {
        $tenant = app(TenantContext::class)->get();
        if (!$tenant || !$tenant->hasModule('abandoned_cart') || empty($cart)) {
            return;
        }

        TenantAbandonedCart::updateOrCreate(
            ['tenant_id' => $tenant->id, 'session_id' => session()->getId()],
            [
                'customer_email' => $contact['email'] ?? null,
                'customer_phone' => $contact['phone'] ?? null,
                'cart_data' => array_values($cart),
                'subtotal' => $this->cartSubtotal($cart),
                'recovered_at' => null,
            ]
        );
    }

    private function markCartRecovered(): void
    {
        $tenant = app(TenantContext::class)->get();
        if (!$tenant || !$tenant->hasModule('abandoned_cart')) {
            return;
        }

        TenantAbandonedCart::where('tenant_id', $tenant->id)
            ->where('session_id', session()->getId())
            ->whereNull('recovered_at')
            ->update(['recovered_at' => now()]);
    }

    private function awardLoyaltyPoints(TenantOrder $order): void
    {
        $tenant = app(TenantContext::class)->get();
        if (!$tenant || !$tenant->hasModule('loyalty_rewards') || !$order->tenant_customer_id) {
            return;
        }

        $setting = TenantFeatureSetting::where('tenant_id', $tenant->id)->where('feature_key', 'loyalty_rewards')->first();
        $pointsPer100 = max(1, (int) ($setting?->settings['points_per_100'] ?? 1));
        $points = (int) floor(((float) $order->total / 100) * $pointsPer100);
        if ($points < 1) {
            return;
        }

        TenantLoyaltyTransaction::create([
            'tenant_id' => $tenant->id,
            'tenant_customer_id' => $order->tenant_customer_id,
            'tenant_order_id' => $order->id,
            'points' => $points,
            'type' => 'earned',
            'notes' => 'Order ' . $order->order_id,
        ]);
    }

    private function cartLineKey(int $productId, ?int $variantId = null): string
    {
        return $productId . ':' . ($variantId ?: 'base');
    }

    private function cartProductIds(array $cart): array
    {
        return array_values(array_unique(array_map(fn ($item) => (int) ($item['product_id'] ?? 0), $cart)));
    }

    private function whatsappNumber(): string
    {
        $tenant = app(TenantContext::class)->get();

        return preg_replace('/[^0-9]/', '', $tenant->whatsapp_number ?? '');
    }

    private function whatsappOrderUrl(TenantOrder $order): string
    {
        $order->loadMissing('items.product');

        $lines = [
            'New order: ' . $order->order_id,
            'Customer: ' . $order->shipping_name,
            'Phone: ' . $order->shipping_phone,
            'Address: ' . $order->shipping_address . ', ' . $order->shipping_pincode,
            '',
            'Items:',
        ];

        foreach ($order->items as $item) {
            $name = $item->product?->name ?? 'Product';
            $lines[] = '- ' . $name . ' x ' . $item->quantity . ' = Rs ' . number_format((float) $item->unit_price * $item->quantity, 2);
        }

        $lines[] = '';
        $lines[] = 'Total: Rs ' . number_format((float) $order->amount, 2);

        return 'https://wa.me/' . $this->whatsappNumber() . '?text=' . rawurlencode(implode("\n", $lines));
    }

    private function storefrontView(string $page): string
    {
        $tenant = app(TenantContext::class)->get();
        $theme = $tenant ? Theme::where('key', $tenant->template_id)->first() : null;
        $baseTemplate = $theme->base_template ?? null;

        if ($baseTemplate && ViewFacade::exists("tenant-templates.{$baseTemplate}.{$page}")) {
            return "tenant-templates.{$baseTemplate}.{$page}";
        }

        return "tenant-templates.shop.{$page}";
    }

    public function add(Request $request, int $id): RedirectResponse
    {
        $this->requireModule('cart');
        $tenant = app(TenantContext::class)->get();
        $product = TenantProduct::where('tenant_id', $tenant->id)->findOrFail($id);
        $variantId = $request->integer('variant_id') ?: null;
        $variant = null;
        $quantity = max(1, min(99, (int) $request->input('quantity', 1)));

        if ($variantId) {
            $variant = TenantProductVariant::where('tenant_product_id', $product->id)
                ->where('is_active', true)
                ->findOrFail($variantId);
        }

        $cart = $this->getCart();
        $key = $this->cartLineKey($product->id, $variant?->id);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $quantity,
            ];
        }

        $this->saveCart($cart);
        $this->rememberAbandonedCart($cart);

        return back()->with('success', 'Item added to cart.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->requireModule('cart');
        $tenant = app(TenantContext::class)->get();
        $cart = $this->getCart();
        $qty = max(1, (int) $request->input('quantity', 1));

        $key = (string) $id;
        if (!isset($cart[$key])) {
            $key = collect(array_keys($cart))->first(fn ($cartKey) => str_starts_with((string) $cartKey, $id . ':'));
        }

        if ($key && isset($cart[$key])) {
            $cart[$key]['quantity'] = $qty;
            $this->saveCart($cart);
        }

        return redirect()->route('tenant.cart');
    }

    public function remove(Request $request, int $id): RedirectResponse
    {
        $this->requireModule('cart');
        $cart = $this->getCart();
        $key = (string) $id;
        if (isset($cart[$key])) {
            unset($cart[$key]);
        } else {
            foreach (array_keys($cart) as $cartKey) {
                if (str_starts_with((string) $cartKey, $id . ':')) {
                    unset($cart[$cartKey]);
                    break;
                }
            }
        }
        $this->saveCart($cart);
        $this->rememberAbandonedCart($cart);

        return redirect()->route('tenant.cart');
    }

    public function index(): View
    {
        $this->requireModule('cart');
        $tenant = app(TenantContext::class)->get();
        $cart = $this->getCart();
        $ids = $this->cartProductIds($cart);
        $products = TenantProduct::where('tenant_id', $tenant->id)
            ->whereIn('id', $ids)
            ->with(['variants.color', 'variants.size', 'images'])
            ->get()
            ->keyBy('id');
        $total = 0;

        foreach ($cart as $key => &$item) {
            $p = $products->get($item['product_id'] ?? null);
            $variant = !empty($item['variant_id']) ? $p?->variants->firstWhere('id', $item['variant_id']) : null;
            $unitPrice = $variant ? $variant->effective_price : (float) ($p?->price ?? 0);
            $item['product'] = $p;
            $item['variant'] = $variant;
            $item['unit_price'] = $unitPrice;
            $item['subtotal'] = $p ? $unitPrice * $item['quantity'] : 0;
            $total += $item['subtotal'];
        }

        $count = $this->cartCount();

        return view($this->storefrontView('cart'), compact('cart', 'total', 'count', 'tenant'));
    }

    public function checkout(): View|RedirectResponse
    {
        $this->requireModule('checkout');
        $tenant = app(TenantContext::class)->get();
        $cart = $this->getCart();

        if (empty($cart)) {
            return redirect()->route('tenant.home')->with('error', 'Your cart is empty.');
        }

        $ids = $this->cartProductIds($cart);
        $products = TenantProduct::where('tenant_id', $tenant->id)
            ->whereIn('id', $ids)
            ->with(['variants.color', 'variants.size', 'images'])
            ->get()
            ->keyBy('id');
        $total = 0;
        $items = [];

        foreach ($cart as $key => $item) {
            $p = $products->get($item['product_id'] ?? null);
            if (!$p) {
                unset($cart[$key]);
                continue;
            }
            $variant = !empty($item['variant_id']) ? $p->variants->firstWhere('id', $item['variant_id']) : null;
            $unitPrice = $variant ? $variant->effective_price : (float) $p->price;
            $subtotal = $unitPrice * $item['quantity'];
            $total += $subtotal;
            $items[] = ['product' => $p, 'variant' => $variant, 'quantity' => $item['quantity'], 'unit_price' => $unitPrice, 'subtotal' => $subtotal];
        }

        $this->saveCart($cart);

        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();
        $hasCod = !$paymentSetting || $paymentSetting->cod_enabled;
        $hasRazorpay = $tenant->hasModule('payments')
            && $paymentSetting
            && $paymentSetting->razorpay_enabled
            && !empty($paymentSetting->api_key)
            && !empty($paymentSetting->api_secret);
        $hasWhatsapp = (!$paymentSetting || $paymentSetting->whatsapp_enabled) && $this->whatsappNumber() !== '';
        $hasCustomPayment = $paymentSetting?->custom_enabled;
        $coupon = null;
        $discountTotal = 0;
        if ($tenant->hasModule('coupons') && session('tenant_coupon_' . $tenant->id)) {
            $coupon = TenantCoupon::where('tenant_id', $tenant->id)->where('code', session('tenant_coupon_' . $tenant->id))->first();
            $discountTotal = $coupon ? $coupon->discountFor($total) : 0;
        }
        $shippingTotal = 0;
        $taxTotal = 0;
        $grandTotal = max(0, $total - $discountTotal + $shippingTotal + $taxTotal);
        $count = $this->cartCount();

        return view($this->storefrontView('checkout'), compact('items', 'total', 'coupon', 'discountTotal', 'shippingTotal', 'taxTotal', 'grandTotal', 'hasCod', 'hasRazorpay', 'hasWhatsapp', 'hasCustomPayment', 'paymentSetting', 'count', 'tenant'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $this->requireModule('checkout');
        $tenant = app(TenantContext::class)->get();
        $cart = $this->getCart();

        if (empty($cart)) {
            return redirect()->route('tenant.home')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'shipping_address' => 'required|string|max:1000',
            'shipping_pincode' => 'required|string|max:10',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,prepaid,whatsapp,custom',
            'coupon_code' => 'nullable|string|max:40',
        ]);

        $this->rememberAbandonedCart($cart, [
            'email' => $validated['customer_email'] ?? null,
            'phone' => $validated['shipping_phone'] ?? null,
        ]);

        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();

        if ($validated['payment_method'] === 'cod' && $paymentSetting && !$paymentSetting->cod_enabled) {
            return back()->withErrors(['payment_method' => 'Cash on Delivery is not available for this store.'])->withInput();
        }

        if ($validated['payment_method'] === 'prepaid') {
            $hasRazorpay = $tenant->hasModule('payments')
                && $paymentSetting
                && $paymentSetting->razorpay_enabled
                && !empty($paymentSetting->api_key)
                && !empty($paymentSetting->api_secret);
            if (!$hasRazorpay) {
                return back()->withErrors(['payment_method' => 'Prepaid is not available for this store.'])->withInput();
            }
        }

        if ($validated['payment_method'] === 'whatsapp' && (($paymentSetting && !$paymentSetting->whatsapp_enabled) || $this->whatsappNumber() === '')) {
            return back()->withErrors(['payment_method' => 'WhatsApp checkout is not configured for this store.'])->withInput();
        }

        if ($validated['payment_method'] === 'custom' && (!$paymentSetting || !$paymentSetting->custom_enabled)) {
            return back()->withErrors(['payment_method' => 'Custom payment is not available for this store.'])->withInput();
        }

        $ids = $this->cartProductIds($cart);
        $products = TenantProduct::where('tenant_id', $tenant->id)
            ->whereIn('id', $ids)
            ->with(['variants.color', 'variants.size'])
            ->get()
            ->keyBy('id');
        $total = 0;
        $orderItems = [];

        foreach ($cart as $item) {
            $p = $products->get($item['product_id'] ?? null);
            if (!$p) continue;
            $variant = !empty($item['variant_id']) ? $p->variants->firstWhere('id', $item['variant_id']) : null;
            $qty = max(1, (int) $item['quantity']);
            $unitPrice = $variant ? $variant->effective_price : (float) $p->price;
            $lineTotal = $unitPrice * $qty;

            if ($tenant->hasModule('inventory')) {
                if ($variant && $variant->stock < $qty) {
                    return back()->withErrors(['payment_method' => 'Insufficient stock for ' . $p->name . '.'])->withInput();
                }
                if (!$variant && $p->stock > 0 && $p->stock < $qty) {
                    return back()->withErrors(['payment_method' => 'Insufficient stock for ' . $p->name . '.'])->withInput();
                }
            }

            $total += $lineTotal;
            $orderItems[] = [
                'tenant_product_id' => $p->id,
                'tenant_product_variant_id' => $variant?->id,
                'product_name' => $p->name,
                'color_name' => $variant?->color?->color_name,
                'size_label' => $variant?->size?->size_label,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $lineTotal,
            ];
        }

        if (empty($orderItems)) {
            return redirect()->route('tenant.home')->with('error', 'Cart contains invalid items.');
        }

        $coupon = null;
        $discountTotal = 0;
        if ($tenant->hasModule('coupons') && !empty($validated['coupon_code'])) {
            $coupon = TenantCoupon::where('tenant_id', $tenant->id)->where('code', strtoupper($validated['coupon_code']))->first();
            $discountTotal = $coupon ? $coupon->discountFor($total) : 0;
        }

        $shippingTotal = 0;
        if ($tenant->hasModule('shipping_rules')) {
            $rule = TenantShippingRule::where('tenant_id', $tenant->id)->where('is_active', true)->get()->first(fn ($r) => $r->appliesTo($validated['shipping_pincode'], $total));
            $shippingTotal = $rule ? $rule->feeFor($total) : 0;
        }

        $taxTotal = $tenant->hasModule('gst_invoice') ? round(max(0, $total - $discountTotal + $shippingTotal) * 0.18, 2) : 0;
        $grandTotal = max(0, $total - $discountTotal + $shippingTotal + $taxTotal);

        $order = TenantOrder::create([
            'tenant_id' => $tenant->id,
            'tenant_customer_id' => session('tenant_customer_' . $tenant->id),
            'order_id' => 'ORD-' . $tenant->id . '-' . time(),
            'amount' => $grandTotal,
            'subtotal' => $total,
            'coupon_code' => $coupon?->code,
            'discount_total' => $discountTotal,
            'shipping_total' => $shippingTotal,
            'tax_total' => $taxTotal,
            'total' => $grandTotal,
            'invoice_number' => $tenant->hasModule('gst_invoice') ? 'INV-' . $tenant->id . '-' . time() : null,
            'currency' => 'INR',
            'status' => $validated['payment_method'] === 'prepaid' ? 'awaiting_payment' : 'pending',
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_method'] === 'prepaid' ? 'pending' : 'unpaid',
            'customer_details' => [
                'name' => $validated['shipping_name'],
                'phone' => $validated['shipping_phone'],
                'email' => $validated['customer_email'] ?? null,
            ],
            'shipping_name' => $validated['shipping_name'],
            'shipping_phone' => $validated['shipping_phone'],
            'customer_email' => $validated['customer_email'] ?? null,
            'shipping_address' => $validated['shipping_address'],
            'shipping_pincode' => $validated['shipping_pincode'],
            'notes' => $validated['notes'] ?? null,
        ]);
        if ($coupon && $discountTotal > 0) {
            $coupon->increment('used_count');
        }

        foreach ($orderItems as $item) {
            $order->items()->create($item);
            if ($tenant->hasModule('inventory')) {
                if (!empty($item['tenant_product_variant_id'])) {
                    TenantProductVariant::whereKey($item['tenant_product_variant_id'])->decrement('stock', $item['quantity']);
                } else {
                    TenantProduct::whereKey($item['tenant_product_id'])->where('stock', '>', 0)->decrement('stock', $item['quantity']);
                }
            }
        }

        $this->markCartRecovered();
        $this->awardLoyaltyPoints($order);
        $this->saveCart([]);

        session()->flash('order_id', $order->id);

        if ($validated['payment_method'] === 'prepaid') {
            return redirect()->route('tenant.checkout.pay', $order->id);
        }

        if ($validated['payment_method'] === 'whatsapp') {
            return redirect()->away($this->whatsappOrderUrl($order));
        }

        return redirect()->route('tenant.checkout.confirm', $order->id);
    }

    public function pay(int $id): View
    {
        $this->requireModule('checkout');
        $tenant = app(TenantContext::class)->get();
        $order = TenantOrder::where('tenant_id', $tenant->id)->findOrFail($id);

        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();
        abort_if(!$paymentSetting || !$paymentSetting->razorpay_enabled || empty($paymentSetting->api_key) || empty($paymentSetting->api_secret), 404);

        abort_if(in_array($order->payment_status, ['paid', 'captured'], true), 409, 'This order has already been paid.');

        if (empty($order->payment_order_id)) {
            try {
                $gatewayOrder = (new \Razorpay\Api\Api($paymentSetting->api_key, $paymentSetting->api_secret))
                    ->order
                    ->create([
                        'amount' => (int) round((float) $order->amount * 100),
                        'currency' => $order->currency ?: 'INR',
                        'receipt' => substr('ehlom_order_' . $tenant->id . '_' . $order->id, 0, 40),
                        'payment_capture' => 1,
                        'notes' => [
                            'order_id' => (string) $order->id,
                            'tenant_id' => (string) $tenant->id,
                            'order_reference' => $order->order_id,
                        ],
                    ]);

                $order->update(['payment_order_id' => $gatewayOrder->id]);
            } catch (\Throwable $exception) {
                report($exception);
                abort(502, 'Unable to start the online payment. Please try again shortly.');
            }
        }

        return view($this->storefrontView('pay'), compact('order', 'paymentSetting', 'tenant'));
    }

    public function verifyRazorpayPayment(Request $request, int $id): JsonResponse
    {
        $this->requireModule('checkout');
        $tenant = app(TenantContext::class)->get();
        $order = TenantOrder::where('tenant_id', $tenant->id)->findOrFail($id);
        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();

        abort_if(!$paymentSetting || !$paymentSetting->razorpay_enabled || empty($paymentSetting->api_key) || empty($paymentSetting->api_secret), 404);

        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:100'],
            'razorpay_order_id' => ['required', 'string', 'max:100'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
        ]);

        if ($order->payment_order_id !== $data['razorpay_order_id']) {
            return response()->json(['message' => 'The payment does not belong to this order.'], 422);
        }

        try {
            $razorpay = new \Razorpay\Api\Api($paymentSetting->api_key, $paymentSetting->api_secret);
            $razorpay->utility->verifyPaymentSignature($data);
            $payment = $razorpay->payment->fetch($data['razorpay_payment_id']);

            if ((int) $payment->amount !== (int) round((float) $order->amount * 100) || $payment->currency !== ($order->currency ?: 'INR')) {
                return response()->json(['message' => 'The Razorpay payment amount does not match this order.'], 422);
            }

            if (!in_array($payment->status, ['captured', 'authorized'], true)) {
                return response()->json(['message' => 'Razorpay has not confirmed this payment yet.'], 422);
            }

            $order->update([
                'status' => $payment->status === 'captured' ? 'confirmed' : 'awaiting_payment',
                'payment_status' => $payment->status === 'captured' ? 'paid' : 'authorized',
                'payment_id' => $data['razorpay_payment_id'],
                'payment_method' => $payment->method ?? 'razorpay',
                'customer_details' => array_merge($order->customer_details ?? [], [
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                    'razorpay_order_id' => $data['razorpay_order_id'],
                    'email' => $payment->email ?? ($order->customer_details['email'] ?? null),
                    'contact' => $payment->contact ?? ($order->customer_details['phone'] ?? null),
                ]),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'We could not verify the Razorpay payment.'], 422);
        }

        return response()->json([
            'redirect' => route('tenant.checkout.confirm', $order->id),
        ]);
    }

    public function confirm(int $id): View
    {
        $this->requireModule('checkout');
        $tenant = app(TenantContext::class)->get();
        $order = TenantOrder::where('tenant_id', $tenant->id)->findOrFail($id);

        $order->load('items.product');
        $count = 0;

        return view($this->storefrontView('confirm'), compact('order', 'count', 'tenant'));
    }
}
