<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->invoice_number ?? $order->order_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color:#111827; margin:0; background:#f8fafc; }
        .invoice { max-width:820px; margin:32px auto; background:#fff; padding:32px; border:1px solid #e5e7eb; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th, td { text-align:left; padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px; }
        .right { text-align:right; }
        .muted { color:#64748b; font-size:13px; line-height:1.6; }
        @media print { body { background:#fff; } .invoice { margin:0; border:0; max-width:none; } .no-print { display:none; } }
    </style>
</head>
<body>
<div class="invoice">
    <button class="no-print" onclick="window.print()" style="float:right;padding:8px 12px;">Print</button>
    <h1>GST Invoice</h1>
    <div class="muted">
        <strong>{{ $tenant->name }}</strong><br>
        Invoice: {{ $order->invoice_number ?? $order->order_id }}<br>
        Order: {{ $order->order_id }}<br>
        Date: {{ $order->created_at->format('d M Y') }}
    </div>
    <h3>Bill To</h3>
    <div class="muted">{{ $order->shipping_name }}<br>{{ $order->shipping_phone }}<br>{{ $order->shipping_address }} {{ $order->shipping_pincode }}</div>
    <table>
        <thead><tr><th>Item</th><th class="right">Qty</th><th class="right">Rate</th><th class="right">Total</th></tr></thead>
        <tbody>
        @foreach ($order->items as $item)
            <tr><td>{{ $item->product_name ?: $item->product?->name }}</td><td class="right">{{ $item->quantity }}</td><td class="right">₹{{ number_format($item->unit_price, 2) }}</td><td class="right">₹{{ number_format($item->total_price, 2) }}</td></tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="right">Subtotal</td><td class="right">₹{{ number_format($order->subtotal, 2) }}</td></tr>
            <tr><td colspan="3" class="right">Discount</td><td class="right">-₹{{ number_format($order->discount_total, 2) }}</td></tr>
            <tr><td colspan="3" class="right">Shipping</td><td class="right">₹{{ number_format($order->shipping_total, 2) }}</td></tr>
            <tr><td colspan="3" class="right">GST</td><td class="right">₹{{ number_format($order->tax_total, 2) }}</td></tr>
            <tr><td colspan="3" class="right"><strong>Total</strong></td><td class="right"><strong>₹{{ number_format($order->total, 2) }}</strong></td></tr>
        </tfoot>
    </table>
</div>
</body>
</html>
