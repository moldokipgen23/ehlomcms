@php
    $tenant = app(\App\Services\TenantContext::class)->get();
    if (!$tenant) return;
    $number = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number ?? '');
    $paymentSetting = $tenant->action_type === 'razorpay'
        ? \App\Models\PaymentSetting::where('tenant_id', $tenant->id)->first()
        : null;
@endphp

@if ($tenant->action_type === 'whatsapp' || ($tenant->action_type === 'razorpay' && !$paymentSetting && $number))
    @php
        $message = rawurlencode($message ?? ($product->name ?? '') . ' - I\'m interested');
        $href = $number ? "https://wa.me/{$number}?text={$message}" : '#';
    @endphp
    @if ($number)
        <a href="{{ $href }}" target="_blank" rel="noopener"
           class="eos-btn eos-btn-primary" style="text-decoration:none;{{ $attributes->get('class') ?? '' }}">
            <i class="ti ti-brand-whatsapp"></i> {{ $label ?? 'Buy Now' }}
        </a>
    @else
        <span class="eos-btn eos-btn-secondary" style="opacity:0.5;cursor:not-allowed;">
            <i class="ti ti-brand-whatsapp"></i> WhatsApp not configured
        </span>
    @endif
@elseif ($tenant->action_type === 'razorpay')
    @if ($paymentSetting && isset($product))
        <button type="button"
                class="eos-btn eos-btn-primary razorpay-btn"
                style="text-decoration:none;{{ $attributes->get('class') ?? '' }}"
                data-key="{{ $paymentSetting->api_key }}"
                data-amount="{{ $product->price * 100 }}"
                data-currency="INR"
                data-name="{{ $tenant->name }}"
                data-description="{{ $product->name }}"
                data-product-id="{{ $product->id }}"
                data-tenant-id="{{ $tenant->id }}">
            <i class="ti ti-credit-card"></i> {{ $label ?? 'Buy Now' }}
        </button>
    @elseif (!$paymentSetting)
        <span class="eos-btn eos-btn-secondary" style="opacity:0.5;cursor:not-allowed;">
            <i class="ti ti-credit-card"></i> Payment not configured
        </span>
    @endif
@endif
