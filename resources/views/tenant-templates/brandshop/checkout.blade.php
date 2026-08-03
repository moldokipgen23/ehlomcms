<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name }} — Checkout</title>
    @if (($tenant->theme_settings['favicon'] ?? null))
        <link rel="icon" href="{{ Storage::url($tenant->theme_settings['favicon']) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .tp-wrap { min-height: 100vh; display: flex; flex-direction: column; }
        .tp-header { display: flex; align-items: center; padding: 16px 32px; border-bottom: 1px solid var(--border); background: var(--bg-card); }
        .tp-back { color: var(--text-muted); text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 4px; }
        .tp-back:hover { color: var(--text-primary); }
        .tp-body { display: flex; gap: 32px; padding: 32px; max-width: 960px; margin: 0 auto; width: 100%; }
        .tp-form-col { flex: 1; min-width: 0; }
        .tp-summary-col { width: 340px; flex-shrink: 0; }
        .tp-section-title { font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 20px; }
        .tp-field { margin-bottom: 14px; }
        .tp-field label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px; }
        .tp-field input, .tp-field textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-card); color: var(--text-primary); font-size: 14px; font-family: inherit; }
        .tp-field input:focus, .tp-field textarea:focus { outline: none; border-color: var(--tp-accent, var(--accent-teal)); box-shadow: 0 0 0 3px rgba(79,142,247,.15); }
        .tp-field textarea { resize: vertical; min-height: 70px; }
        .tp-error { color: #ef4444; font-size: 11px; margin-top: 3px; }
        .tp-payment-options { display: flex; gap: 10px; margin: 16px 0; flex-wrap: wrap; }
        .tp-payment-option { flex: 1 1 130px; padding: 14px; border: 2px solid var(--border); border-radius: 10px; cursor: pointer; text-align: center; transition: .15s; }
        .tp-payment-option:hover { border-color: var(--tp-accent, var(--accent-teal)); }
        .tp-payment-option.selected { border-color: var(--tp-accent, var(--accent-teal)); background: rgba(79,142,247,.06); }
        .tp-payment-option input { display: none; }
        .tp-payment-option i { font-size: 24px; display: block; margin-bottom: 6px; color: var(--text-secondary); }
        .tp-payment-option.selected i { color: var(--tp-accent, var(--accent-teal)); }
        .tp-payment-option span { display: block; font-size: 12px; font-weight: 600; color: var(--text-primary); }
        .tp-checkout-note { margin: 0 0 16px; padding: 12px 14px; border: 1px solid rgba(79,142,247,.2); border-radius: 10px; background: rgba(79,142,247,.06); color: var(--text-secondary); font-size: 12.5px; line-height: 1.6; }
        .tp-summary-card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 11px; padding: 18px; }
        .tp-summary-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
        .tp-summary-item:last-child { border-bottom: none; }
        .tp-summary-name { color: var(--text-primary); }
        .tp-summary-qty { color: var(--text-muted); font-size: 11px; }
        .tp-summary-price { color: var(--text-primary); font-weight: 600; }
        .tp-total-row { display: flex; justify-content: space-between; padding: 12px 0 0; margin-top: 8px; border-top: 2px solid var(--border); font-size: 16px; font-weight: 700; color: var(--tp-accent, var(--accent-teal)); }
        .eos-btn { display: inline-flex; align-items: center; gap: 6px; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; width: 100%; justify-content: center; }
        .eos-btn-primary { background: var(--tp-accent, var(--accent-teal)); color: #fff; }
        .eos-btn-primary:hover { opacity: .9; }
        .eos-btn:disabled { opacity: .5; cursor: not-allowed; }
        .tp-foot { text-align: center; padding: 20px 32px; font-size: 11px; color: var(--text-dim); border-top: 1px solid var(--border); margin-top: auto; }
        @media (max-width: 720px) { .tp-body { flex-direction: column; } .tp-summary-col { width: 100%; } }
    </style>
</head>
<body class="antialiased">
    @php $ts = $tenant->theme_settings ?? []; $accent = $ts['accent_color'] ?? '#2563eb'; @endphp
    <div class="tp-wrap" style="--tp-accent: {{ $accent }};">
        <div class="tp-header">
            <a href="{{ route('tenant.cart') }}" class="tp-back"><i class="ti ti-arrow-left"></i> Back to cart</a>
        </div>

        <form action="{{ route('tenant.checkout.place') }}" method="POST" class="tp-body">
            @csrf

            <div class="tp-form-col">
                <div class="tp-section-title">Shipping Address</div>

                <div class="tp-field">
                    <label for="shipping_name">Full Name</label>
                    <input type="text" name="shipping_name" id="shipping_name" value="{{ old('shipping_name') }}" required>
                    @error('shipping_name')<div class="tp-error">{{ $message }}</div>@enderror
                </div>

                <div class="tp-field">
                    <label for="shipping_phone">Phone Number</label>
                    <input type="text" name="shipping_phone" id="shipping_phone" value="{{ old('shipping_phone') }}" required>
                    @error('shipping_phone')<div class="tp-error">{{ $message }}</div>@enderror
                </div>

                <div class="tp-field">
                    <label for="customer_email">Email Address</label>
                    <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email') }}">
                    @error('customer_email')<div class="tp-error">{{ $message }}</div>@enderror
                </div>

                <div class="tp-field">
                    <label for="shipping_address">Address</label>
                    <textarea name="shipping_address" id="shipping_address" required>{{ old('shipping_address') }}</textarea>
                    @error('shipping_address')<div class="tp-error">{{ $message }}</div>@enderror
                </div>

                <div class="tp-field">
                    <label for="shipping_pincode">Pincode</label>
                    <input type="text" name="shipping_pincode" id="shipping_pincode" value="{{ old('shipping_pincode') }}" required>
                    @error('shipping_pincode')<div class="tp-error">{{ $message }}</div>@enderror
                </div>

                <div class="tp-field">
                    <label for="notes">Order Notes</label>
                    <textarea name="notes" id="notes">{{ old('notes') }}</textarea>
                    @error('notes')<div class="tp-error">{{ $message }}</div>@enderror
                </div>
                @if ($tenant->hasModule('coupons'))
                    <div class="tp-field">
                        <label for="coupon_code">Coupon Code</label>
                        <input type="text" name="coupon_code" id="coupon_code" value="{{ old('coupon_code', $coupon?->code) }}" placeholder="WELCOME10">
                    </div>
                @endif

                <div class="tp-section-title" style="margin-top:24px;">Payment Method</div>
                @error('payment_method')<div class="tp-error" style="margin-bottom:8px;">{{ $message }}</div>@enderror

                <div class="tp-payment-options">
                    @if ($hasCod)
                        <label class="tp-payment-option" id="opt-cod" onclick="selectPayment('cod')">
                            <input type="radio" name="payment_method" value="cod" {{ old('payment_method')==='cod' ? 'checked' : '' }}>
                            <i class="ti ti-cash"></i>
                            <span>Cash on Delivery</span>
                        </label>
                    @endif
                    @if ($hasRazorpay)
                        <label class="tp-payment-option" id="opt-prepaid" onclick="selectPayment('prepaid')">
                            <input type="radio" name="payment_method" value="prepaid" {{ old('payment_method')==='prepaid' ? 'checked' : '' }}>
                            <i class="ti ti-credit-card"></i>
                            <span>Card / UPI</span>
                        </label>
                    @endif
                    @if ($hasWhatsapp)
                        <label class="tp-payment-option" id="opt-whatsapp" onclick="selectPayment('whatsapp')">
                            <input type="radio" name="payment_method" value="whatsapp" {{ old('payment_method')==='whatsapp' ? 'checked' : '' }}>
                            <i class="ti ti-brand-whatsapp"></i>
                            <span>{{ $ts['whatsapp_order_text'] ?? 'WhatsApp Order' }}</span>
                        </label>
                    @endif
                    @if ($hasCustomPayment)
                        <label class="tp-payment-option" id="opt-custom" onclick="selectPayment('custom')">
                            <input type="radio" name="payment_method" value="custom" {{ old('payment_method')==='custom' ? 'checked' : '' }}>
                            <i class="ti ti-building-bank"></i>
                            <span>{{ $paymentSetting->custom_label ?: 'Custom Payment' }}</span>
                        </label>
                    @endif
                </div>
                @if ($hasCustomPayment && $paymentSetting->custom_instructions)
                    <div class="tp-help" style="margin:-4px 0 12px;">{{ $paymentSetting->custom_instructions }}</div>
                @endif
                @if (!empty($ts['checkout_note']))
                    <div class="tp-checkout-note">{{ $ts['checkout_note'] }}</div>
                @endif

                <button type="submit" class="eos-btn eos-btn-primary" style="margin-top:8px;">
                    <i class="ti ti-shopping-cart-check"></i> {{ $ts['checkout_button_text'] ?? 'Place Order' }}
                </button>
            </div>

            <div class="tp-summary-col">
                <div class="tp-section-title">Order Summary</div>
                <div class="tp-summary-card">
                    @foreach ($items as $item)
                        <div class="tp-summary-item">
                            <div>
                                <div class="tp-summary-name">{{ $item['product']->name }}</div>
                                @if (!empty($item['variant']))
                                    <div class="tp-summary-qty">
                                        {{ $item['variant']->color?->color_name }}
                                        @if ($item['variant']->size) / {{ $item['variant']->size->size_label }} @endif
                                    </div>
                                @endif
                                <div class="tp-summary-qty">Qty: {{ $item['quantity'] }}</div>
                            </div>
                            <div class="tp-summary-price">₹{{ number_format($item['subtotal'], 2) }}</div>
                        </div>
                    @endforeach
                    <div class="tp-total-row">
                        <span>Subtotal</span>
                        <span>₹{{ number_format($total, 2) }}</span>
                    </div>
                    @if ($discountTotal > 0)
                        <div class="tp-summary-item"><span>Discount {{ $coupon?->code }}</span><span>-₹{{ number_format($discountTotal, 2) }}</span></div>
                    @endif
                    @if ($shippingTotal > 0)
                        <div class="tp-summary-item"><span>Shipping</span><span>₹{{ number_format($shippingTotal, 2) }}</span></div>
                    @endif
                    @if ($taxTotal > 0)
                        <div class="tp-summary-item"><span>GST</span><span>₹{{ number_format($taxTotal, 2) }}</span></div>
                    @endif
                    <div class="tp-total-row"><span>Total</span><span>₹{{ number_format($grandTotal, 2) }}</span></div>
                </div>
            </div>
        </form>

        <div class="tp-foot">{{ $tenant->name }} &middot; Powered by Ehlom OS</div>
    </div>

    <script>
        function selectPayment(val) {
            const option = document.getElementById('opt-' + val);
            const input = document.querySelector('input[name="payment_method"][value="' + val + '"]');
            if (!option || !input) return;

            document.querySelectorAll('.tp-payment-option').forEach(el => el.classList.remove('selected'));
            option.classList.add('selected');
            input.checked = true;
        }
        @if (old('payment_method'))
            selectPayment('{{ old('payment_method') }}');
        @else
            @if ($hasCod)
                selectPayment('cod');
            @elseif ($hasRazorpay)
                selectPayment('prepaid');
            @elseif ($hasWhatsapp)
                selectPayment('whatsapp');
            @elseif ($hasCustomPayment)
                selectPayment('custom');
            @endif
        @endif
    </script>
</body>
</html>
