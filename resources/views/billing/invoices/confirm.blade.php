<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Payment received - Ehlom</title>
<style>body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;box-sizing:border-box;background:#f1f5f9;color:#172033;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.card{max-width:460px;background:#fff;border:1px solid #dbe3ef;border-radius:12px;padding:32px;text-align:center;box-shadow:0 20px 45px rgba(15,23,42,.1)}.check{color:#16a34a;font-size:42px}h1{margin:12px 0 8px}.muted{color:#64748b;line-height:1.55}</style></head>
<body><main class="card"><div class="check">&#10003;</div><h1>Payment received</h1><div class="muted">Invoice <strong>{{ $invoice->invoice_number }}</strong> has been paid. Thank you.</div></main></body>
</html>
