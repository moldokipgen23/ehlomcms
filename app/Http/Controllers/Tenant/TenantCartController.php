<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\TenantOrder;
use App\Models\TenantProduct;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantCartController extends Controller
{
    private const CART_KEY = 'tenant_cart_';

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

    public function add(Request $request, string $subdomain, int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $product = TenantProduct::where('tenant_id', $tenant->id)->findOrFail($id);

        $cart = $this->getCart();

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'quantity' => 1,
            ];
        }

        $this->saveCart($cart);

        return back()->with('success', 'Item added to cart.');
    }

    public function update(Request $request, string $subdomain, int $id): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $product = TenantProduct::where('tenant_id', $tenant->id)->findOrFail($id);

        $cart = $this->getCart();
        $qty = max(1, (int) $request->input('quantity', 1));

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = $qty;
            $this->saveCart($cart);
        }

        return redirect()->route('tenant.cart');
    }

    public function remove(Request $request, string $subdomain, int $id): RedirectResponse
    {
        $cart = $this->getCart();
        unset($cart[$id]);
        $this->saveCart($cart);

        return redirect()->route('tenant.cart');
    }

    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();
        $cart = $this->getCart();
        $ids = array_keys($cart);
        $products = TenantProduct::where('tenant_id', $tenant->id)->whereIn('id', $ids)->get()->keyBy('id');
        $total = 0;

        foreach ($cart as $id => &$item) {
            $p = $products->get($id);
            $item['product'] = $p;
            $item['subtotal'] = $p ? $p->price * $item['quantity'] : 0;
            $total += $item['subtotal'];
        }

        $count = $this->cartCount();

        return view('tenant-templates.shop.cart', compact('cart', 'total', 'count', 'tenant'));
    }

    public function checkout(): View
    {
        $tenant = app(TenantContext::class)->get();
        $cart = $this->getCart();

        if (empty($cart)) {
            return redirect()->route('tenant.home')->with('error', 'Your cart is empty.');
        }

        $ids = array_keys($cart);
        $products = TenantProduct::where('tenant_id', $tenant->id)->whereIn('id', $ids)->get()->keyBy('id');
        $total = 0;
        $items = [];

        foreach ($cart as $id => $item) {
            $p = $products->get($id);
            if (!$p) {
                unset($cart[$id]);
                continue;
            }
            $subtotal = $p->price * $item['quantity'];
            $total += $subtotal;
            $items[] = ['product' => $p, 'quantity' => $item['quantity'], 'subtotal' => $subtotal];
        }

        $this->saveCart($cart);

        $hasRazorpay = $tenant->hasModule('payments') && PaymentSetting::where('tenant_id', $tenant->id)->exists();
        $count = $this->cartCount();

        return view('tenant-templates.shop.checkout', compact('items', 'total', 'hasRazorpay', 'count', 'tenant'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        $cart = $this->getCart();

        if (empty($cart)) {
            return redirect()->route('tenant.home')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:1000',
            'shipping_pincode' => 'required|string|max:10',
            'payment_method' => 'required|in:cod,prepaid',
        ]);

        if ($validated['payment_method'] === 'prepaid') {
            $hasRazorpay = $tenant->hasModule('payments') && PaymentSetting::where('tenant_id', $tenant->id)->exists();
            if (!$hasRazorpay) {
                return back()->withErrors(['payment_method' => 'Prepaid is not available for this store.'])->withInput();
            }
        }

        $ids = array_keys($cart);
        $products = TenantProduct::where('tenant_id', $tenant->id)->whereIn('id', $ids)->get()->keyBy('id');
        $total = 0;
        $orderItems = [];

        foreach ($cart as $id => $item) {
            $p = $products->get($id);
            if (!$p) continue;
            $total += $p->price * $item['quantity'];
            $orderItems[] = [
                'tenant_product_id' => $p->id,
                'quantity' => $item['quantity'],
                'unit_price' => $p->price,
            ];
        }

        if (empty($orderItems)) {
            return redirect()->route('tenant.home')->with('error', 'Cart contains invalid items.');
        }

        $order = TenantOrder::create([
            'tenant_id' => $tenant->id,
            'order_id' => 'ORD-' . $tenant->id . '-' . time(),
            'amount' => $total,
            'currency' => 'INR',
            'status' => $validated['payment_method'] === 'prepaid' ? 'awaiting_payment' : 'pending',
            'payment_method' => $validated['payment_method'],
            'customer_details' => [
                'name' => $validated['shipping_name'],
                'phone' => $validated['shipping_phone'],
            ],
            'shipping_name' => $validated['shipping_name'],
            'shipping_phone' => $validated['shipping_phone'],
            'shipping_address' => $validated['shipping_address'],
            'shipping_pincode' => $validated['shipping_pincode'],
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        $this->saveCart([]);

        session()->flash('order_id', $order->id);

        if ($validated['payment_method'] === 'prepaid') {
            return redirect()->route('tenant.checkout.pay', $order->id);
        }

        return redirect()->route('tenant.checkout.confirm', $order->id);
    }

    public function pay(string $subdomain, int $id): View
    {
        $tenant = app(TenantContext::class)->get();
        $order = TenantOrder::where('tenant_id', $tenant->id)->findOrFail($id);

        $paymentSetting = PaymentSetting::where('tenant_id', $tenant->id)->first();

        return view('tenant-templates.shop.pay', compact('order', 'paymentSetting', 'tenant'));
    }

    public function confirm(string $subdomain, int $id): View
    {
        $tenant = app(TenantContext::class)->get();
        $order = TenantOrder::where('tenant_id', $tenant->id)->findOrFail($id);

        $order->load('items.product');
        $count = 0;

        return view('tenant-templates.shop.confirm', compact('order', 'count', 'tenant'));
    }
}
