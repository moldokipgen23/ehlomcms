<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pay {{ $invoice->invoice_number }} - Ehlom</title>
    @if ($billingMethods['razorpay'])
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endif
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; min-height: 100vh; background: #f1f5f9; color: #172033; display: grid; place-items: center; padding: 24px; box-sizing: border-box; }
        .pay-card { width: min(100%, 460px); background: #fff; border: 1px solid #dbe3ef; border-radius: 12px; padding: 32px; box-shadow: 0 20px 45px rgba(15, 23, 42, .10); }
        .eyebrow { color: #2563eb; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        h1 { font-size: 26px; margin: 10px 0 6px; }
        .muted { color: #64748b; line-height: 1.55; }
        .amount { margin: 24px 0; padding: 20px; border-radius: 10px; background: #eff6ff; color: #1d4ed8; font-weight: 800; font-size: 34px; }
        button { width: 100%; border: 0; border-radius: 8px; padding: 14px 18px; background: #2563eb; color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; }
        button:disabled { opacity: .65; cursor: wait; }
        .secure { margin: 16px 0 0; text-align: center; color: #64748b; font-size: 12px; }
        .methods { margin-top: 18px; display: grid; gap: 12px; }
        .method { padding: 16px; border: 1px solid #dbe3ef; border-radius: 10px; background: #f8fafc; }
        .method h2 { margin: 0 0 7px; font-size: 15px; }
        .method p { margin: 0; white-space: pre-line; color: #475569; font-size: 13px; line-height: 1.55; }
        .method-note { margin-top: 8px !important; color: #64748b !important; font-size: 12px !important; }
    </style>
</head>
<body>
    <main class="pay-card">
        <div class="eyebrow">Ehlom invoice payment</div>
        <h1>{{ $invoice->invoice_number }}</h1>
        <div class="muted">{{ $invoice->client?->name }} - invoice payment</div>
        <div class="amount">₹{{ number_format($invoice->total, 2) }}</div>
        @if ($billingMethods['razorpay'])
            <button id="pay-button" type="button">Pay securely with Razorpay</button>
        @endif
        @if ($billingMethods['bank'] || $billingMethods['cash'])
            <div class="methods">
                @if ($billingMethods['bank'])
                    <section class="method">
                        <h2>{{ $billingMethods['bank_label'] ?: 'Bank transfer / UPI' }}</h2>
                        <p>{{ $billingMethods['bank_instructions'] ?: 'Contact Ehlom for the bank transfer or UPI details for this invoice.' }}</p>
                        <p class="method-note">Ehlom will confirm the transfer before marking this invoice paid.</p>
                    </section>
                @endif
                @if ($billingMethods['cash'])
                    <section class="method">
                        <h2>Cash / manual payment</h2>
                        <p>{{ $billingMethods['cash_instructions'] ?: 'Contact Ehlom to arrange this payment.' }}</p>
                        <p class="method-note">Ehlom will confirm receipt before marking this invoice paid.</p>
                    </section>
                @endif
            </div>
        @endif
        <div class="secure">Online payments are verified automatically. Manual payments are confirmed by Ehlom after receipt.</div>
    </main>
    @if ($billingMethods['razorpay'])
    <script>
        document.getElementById('pay-button').addEventListener('click', function () {
            const button = this;
            button.disabled = true;
            button.textContent = 'Opening secure payment...';
            const checkout = new Razorpay({
                key: @json($razorpayKey),
                amount: {{ (int) round((float) $payment->amount * 100) }},
                currency: 'INR',
                order_id: @json($payment->razorpay_order_id),
                name: 'Ehlom',
                description: @json('Invoice ' . $invoice->invoice_number),
                handler: async (response) => {
                    try {
                        const verification = await fetch(@json($verifyUrl), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify(response),
                        });
                        const result = await verification.json();
                        if (!verification.ok || !result.redirect) throw new Error(result.message || 'Payment verification failed.');
                        window.location.assign(result.redirect);
                    } catch (error) {
                        button.disabled = false;
                        button.textContent = 'Pay securely with Razorpay';
                        alert(error.message || 'We could not verify your payment. Please contact Ehlom.');
                    }
                },
                modal: { ondismiss: () => { button.disabled = false; button.textContent = 'Pay securely with Razorpay'; } },
            });
            checkout.open();
        });
    </script>
    @endif
</body>
</html>
