<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name }} - Checkout</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/jemdesign-storefront.css') }}">
    <style>
        body { background: var(--black); color: var(--white); }
        .jem-checkout-head { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; justify-content: space-between; padding: 22px clamp(18px, 5vw, 56px); background: rgba(11,11,12,.9); border-bottom: 1px solid rgba(255,255,255,.08); backdrop-filter: blur(18px); }
        .jem-wordmark { font-family: var(--serif); font-size: 32px; font-style: italic; color: var(--white); }
        .jem-back { color: var(--gray); font-size: 11px; letter-spacing: .18em; text-transform: uppercase; }
        .jem-back:hover { color: var(--gold); }
        .jem-checkout { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 54px 0 90px; }
        .jem-page-title { font-family: var(--serif); font-size: clamp(38px, 6vw, 70px); font-weight: 300; margin-bottom: 32px; }
        .jem-grid { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 28px; align-items: start; }
        .jem-card { border: 1px solid rgba(255,255,255,.08); background: linear-gradient(135deg, rgba(255,255,255,.045), rgba(201,160,78,.055)); padding: clamp(22px, 4vw, 36px); }
        .jem-card-title { color: var(--gold); font-size: 11px; letter-spacing: .22em; text-transform: uppercase; margin-bottom: 18px; }
        .jem-field { margin-bottom: 16px; }
        .jem-field label { display: block; color: var(--gray); font-size: 10px; letter-spacing: .18em; text-transform: uppercase; margin-bottom: 8px; }
        .jem-field input, .jem-field textarea { width: 100%; min-height: 50px; padding: 13px 14px; background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.13); color: var(--white); font: inherit; }
        .jem-field textarea { min-height: 92px; resize: vertical; }
        .jem-field input:focus, .jem-field textarea:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,160,78,.12); }
        .jem-error { color: #fca5a5; font-size: 12px; margin-top: 5px; }
        .jem-payments { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 10px 0 22px; }
        .jem-payment { border: 1px solid rgba(255,255,255,.12); padding: 16px; cursor: pointer; color: var(--white-dim); background: rgba(255,255,255,.035); }
        .jem-payment input { display: none; }
        .jem-payment strong { display: block; font-size: 13px; }
        .jem-payment span { display: block; color: var(--gray); font-size: 11px; margin-top: 4px; }
        .jem-payment.selected { border-color: var(--gold); background: rgba(201,160,78,.1); color: var(--white); }
        .jem-summary-line { display: flex; justify-content: space-between; gap: 18px; padding: 13px 0; border-bottom: 1px solid rgba(255,255,255,.08); color: var(--white-dim); font-size: 13px; }
        .jem-summary-line small { display: block; color: var(--gray); margin-top: 4px; }
        .jem-summary-total { display: flex; justify-content: space-between; color: var(--gold); font-size: 22px; padding-top: 18px; }
        @media (max-width: 860px) { .jem-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<header class="jem-checkout-head">
    <a href="{{ route('tenant.home') }}" class="jem-wordmark">jem</a>
    <a href="{{ route('tenant.cart') }}" class="jem-back">Back to Bag</a>
</header>

<main class="jem-checkout">
    <h1 class="jem-page-title">Checkout</h1>
    <form action="{{ route('tenant.checkout.place') }}" method="POST" class="jem-grid">
        @csrf
        <section class="jem-card">
            <div class="jem-card-title">Delivery Details</div>
            <div class="jem-field"><label for="shipping_name">Full Name</label><input id="shipping_name" name="shipping_name" value="{{ old('shipping_name') }}" required>@error('shipping_name')<div class="jem-error">{{ $message }}</div>@enderror</div>
            <div class="jem-field"><label for="shipping_phone">Phone Number</label><input id="shipping_phone" name="shipping_phone" value="{{ old('shipping_phone') }}" required>@error('shipping_phone')<div class="jem-error">{{ $message }}</div>@enderror</div>
            <div class="jem-field"><label for="customer_email">Email</label><input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email') }}">@error('customer_email')<div class="jem-error">{{ $message }}</div>@enderror</div>
            <div class="jem-field"><label for="shipping_address">Address</label><textarea id="shipping_address" name="shipping_address" required>{{ old('shipping_address') }}</textarea>@error('shipping_address')<div class="jem-error">{{ $message }}</div>@enderror</div>
            <div class="jem-field"><label for="shipping_pincode">Pincode</label><input id="shipping_pincode" name="shipping_pincode" value="{{ old('shipping_pincode') }}" required>@error('shipping_pincode')<div class="jem-error">{{ $message }}</div>@enderror</div>
            <div class="jem-field"><label for="notes">Order Notes</label><textarea id="notes" name="notes">{{ old('notes') }}</textarea></div>
            @if ($tenant->hasModule('coupons'))
                <div class="jem-field"><label for="coupon_code">Coupon Code</label><input id="coupon_code" name="coupon_code" value="{{ old('coupon_code', $coupon?->code) }}" placeholder="WELCOME10"></div>
            @endif

            <div class="jem-card-title" style="margin-top:26px;">Payment Method</div>
            @error('payment_method')<div class="jem-error">{{ $message }}</div>@enderror
            <div class="jem-payments">
                @if ($hasCod)
                    <label class="jem-payment" data-payment="cod"><input type="radio" name="payment_method" value="cod"><strong>Cash on Delivery</strong><span>Pay when delivered</span></label>
                @endif
                @if ($hasWhatsapp)
                    <label class="jem-payment" data-payment="whatsapp"><input type="radio" name="payment_method" value="whatsapp"><strong>WhatsApp Order</strong><span>Send order details</span></label>
                @endif
                @if ($hasRazorpay)
                    <label class="jem-payment" data-payment="prepaid"><input type="radio" name="payment_method" value="prepaid"><strong>Card / UPI</strong><span>Razorpay checkout</span></label>
                @endif
                @if ($hasCustomPayment)
                    <label class="jem-payment" data-payment="custom"><input type="radio" name="payment_method" value="custom"><strong>{{ $paymentSetting->custom_label ?: 'Custom Payment' }}</strong><span>Manual payment</span></label>
                @endif
            </div>
            @if ($hasCustomPayment && $paymentSetting->custom_instructions)
                <p style="color:var(--gray);font-size:13px;line-height:1.7;margin-bottom:16px;">{{ $paymentSetting->custom_instructions }}</p>
            @endif
            <button type="submit" class="btn btn--gold btn--full">Place Order</button>
        </section>

        <aside class="jem-card">
            <div class="jem-card-title">Order Summary</div>
            @foreach ($items as $item)
                <div class="jem-summary-line">
                    <span>{{ $item['product']->name }} <small>Qty {{ $item['quantity'] }}</small></span>
                    <strong>₹{{ number_format($item['subtotal'], 2) }}</strong>
                </div>
            @endforeach
            <div class="jem-summary-line"><span>Subtotal</span><strong>₹{{ number_format($total, 2) }}</strong></div>
            @if ($discountTotal > 0)<div class="jem-summary-line"><span>Discount</span><strong>-₹{{ number_format($discountTotal, 2) }}</strong></div>@endif
            @if ($shippingTotal > 0)<div class="jem-summary-line"><span>Shipping</span><strong>₹{{ number_format($shippingTotal, 2) }}</strong></div>@endif
            @if ($taxTotal > 0)<div class="jem-summary-line"><span>GST</span><strong>₹{{ number_format($taxTotal, 2) }}</strong></div>@endif
            <div class="jem-summary-total"><span>Total</span><strong>₹{{ number_format($grandTotal, 2) }}</strong></div>
        </aside>
    </form>
</main>
<script>
    function selectPayment(value) {
        document.querySelectorAll('.jem-payment').forEach(label => {
            const active = label.dataset.payment === value;
            label.classList.toggle('selected', active);
            label.querySelector('input').checked = active;
        });
    }
    document.querySelectorAll('.jem-payment').forEach(label => label.addEventListener('click', () => selectPayment(label.dataset.payment)));
    @if (old('payment_method'))
        selectPayment('{{ old('payment_method') }}');
    @elseif ($hasCod)
        selectPayment('cod');
    @elseif ($hasWhatsapp)
        selectPayment('whatsapp');
    @elseif ($hasRazorpay)
        selectPayment('prepaid');
    @elseif ($hasCustomPayment)
        selectPayment('custom');
    @endif
</script>
</body>
</html>
