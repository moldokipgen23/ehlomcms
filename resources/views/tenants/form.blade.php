@extends('layouts.app')

@section('title', $tenant ? 'Edit Tenant' : 'New Tenant')
@section('subtitle', $tenant ? 'Update tenant settings' : 'Create a new tenant site')

@section('content')
<form method="POST" action="{{ $tenant ? route('tenants.update', $tenant) : route('tenants.store') }}">
@csrf
@if ($tenant) @method('PUT') @endif
<div class="eos-row" style="gap:20px;max-width:1200px;">

    {{-- LEFT: Basic Info + Payment --}}
    <div class="eos-card" style="flex:0 0 400px;max-width:400px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Basic Information</div>
        </div>
        <div class="eos-card-body" style="padding:16px;display:flex;flex-direction:column;gap:14px;">

            <div class="eos-field">
                <label class="eos-label">Subdomain <span class="text-red-500">*</span></label>
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="text" name="subdomain" value="{{ old('subdomain', $tenant->subdomain ?? '') }}" class="eos-input" required style="flex:1;" {{ $tenant ? 'readonly' : '' }}>
                    <span style="color:var(--text-dim);">.{{ config('app.tenant_domain', 'ehlom.com') }}</span>
                </div>
                @error('subdomain') <div class="eos-error">{{ $message }}</div> @enderror
            </div>

            <div class="eos-field">
                <label class="eos-label">Site Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $tenant->name ?? ($prefillClient->business_name ?? $prefillClient->name ?? '')) }}" class="eos-input" required>
                @error('name') <div class="eos-error">{{ $message }}</div> @enderror
            </div>

            <div class="eos-field">
                <label class="eos-label">Business Type <span class="text-red-500">*</span></label>
                <select name="site_type" class="eos-input" required id="siteTypeSelect">
                    @foreach ($businessTypes as $key => $type)
                        <option value="{{ $key }}" {{ (old('site_type', $tenant->site_type ?? '') === $key) ? 'selected' : '' }}>
                            {{ $type['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('site_type') <div class="eos-error">{{ $message }}</div> @enderror
            </div>

            <div class="eos-field">
                <label class="eos-label">Payment Mode</label>
                <select name="action_type" class="eos-input" id="actionTypeSelect">
                    <option value="offline" {{ (old('action_type', $tenant->action_type ?? 'offline') === 'offline') ? 'selected' : '' }}>Offline / Manual</option>
                    <option value="razorpay" {{ (old('action_type', $tenant->action_type ?? '') === 'razorpay') ? 'selected' : '' }}>Razorpay (Online)</option>
                    <option value="stripe" {{ (old('action_type', $tenant->action_type ?? '') === 'stripe') ? 'selected' : '' }}>Stripe (Online)</option>
                    <option value="paypal" {{ (old('action_type', $tenant->action_type ?? '') === 'paypal') ? 'selected' : '' }}>PayPal (Online)</option>
                    <option value="custom" {{ (old('action_type', $tenant->action_type ?? '') === 'custom') ? 'selected' : '' }}>Custom Gateway</option>
                </select>
            </div>

            {{-- CUSTOM GATEWAY FIELDS --}}
            <div class="eos-field custom-gateway-fields" id="customGatewayFields" style="display:none;">
                <label class="eos-label">Gateway Name</label>
                <input type="text" name="custom_gateway_name" value="{{ old('custom_gateway_name', $tenant->custom_gateway_name ?? '') }}" class="eos-input" placeholder="e.g. PhonePe, Paytm, BillDesk">
            </div>
            <div class="eos-field custom-gateway-fields" id="customGatewayFields2" style="display:none;">
                <label class="eos-label">Gateway URL / Endpoint</label>
                <input type="url" name="custom_gateway_url" value="{{ old('custom_gateway_url', $tenant->custom_gateway_url ?? '') }}" class="eos-input" placeholder="https://api.gateway.com/checkout">
            </div>
            <div class="eos-field custom-gateway-fields" id="customGatewayFields3" style="display:none;">
                <label class="eos-label">API Key / Merchant ID</label>
                <input type="text" name="custom_gateway_key" value="{{ old('custom_gateway_key', $tenant->custom_gateway_key ?? '') }}" class="eos-input" placeholder="Your merchant ID or API key">
            </div>
            <div class="eos-field custom-gateway-fields" id="customGatewayFields4" style="display:none;">
                <label class="eos-label">Secret Key / Salt</label>
                <input type="password" name="custom_gateway_secret" value="{{ old('custom_gateway_secret', $tenant->custom_gateway_secret ?? '') }}" class="eos-input" placeholder="Secret key (stored encrypted)">
            </div>
            <div class="eos-field custom-gateway-fields" id="customGatewayFields5" style="display:none;">
                <label class="eos-label">Callback / Webhook URL</label>
                <input type="url" name="custom_gateway_callback" value="{{ old('custom_gateway_callback', $tenant->custom_gateway_callback ?? '') }}" class="eos-input" placeholder="https://yoursite.com/payment/callback">
            </div>

            <div class="eos-field">
                <label class="eos-label">Plan (legacy free-text)</label>
                <input type="text" name="plan" value="{{ old('plan', $tenant->plan ?? '') }}" class="eos-input" placeholder="e.g. Pro, Enterprise">
            </div>

            @if (!$tenant)
                <div class="eos-field" style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px;">
                    <label class="eos-label">Owner Name <span class="text-red-500">*</span></label>
                    <input type="text" name="owner_name" value="{{ old('owner_name', $prefillClient->name ?? '') }}" class="eos-input" required>
                    @error('owner_name') <div class="eos-error">{{ $message }}</div> @enderror
                </div>

                <div class="eos-field">
                    <label class="eos-label">Owner Email <span class="text-red-500">*</span></label>
                    <input type="email" name="owner_email" value="{{ old('owner_email', $prefillClient->email ?? '') }}" class="eos-input" required>
                    @error('owner_email') <div class="eos-error">{{ $message }}</div> @enderror
                </div>
            @endif
        </div>
    </div>

    {{-- RIGHT: Business Type Selection --}}
    <div style="flex:1;min-width:0;">
        <div style="margin-bottom:12px;font-weight:600;font-size:13px;color:var(--text-secondary);">Select Business Type</div>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;">
            @php
                $typeIcons = ['school' => 'ti-school', 'shopping' => 'ti-shopping-cart', 'restaurant' => 'ti-utensils', 'business' => 'ti-briefcase-2'];
                $typeColors = ['school' => '#10b981', 'shopping' => '#f59e0b', 'restaurant' => '#ef4444', 'business' => '#6366f1'];
            @endphp
            @foreach ($businessTypes as $typeKey => $type)
                @php
                    $freeCount = count(array_filter($type['default_modules'] ?? [], fn($m) => $modules[$m]['free'] ?? false));
                    $paidCount = count($type['default_modules'] ?? []) - $freeCount;
                    $themeCount = collect($themes)->filter(fn($t) => in_array($typeKey, $t['industries'] ?? []))->count();
                @endphp
                <label class="business-type-card" data-type="{{ $typeKey }}" style="display:block;border:2px solid var(--border);border-radius:10px;padding:16px;cursor:pointer;transition:all .2s;background:var(--bg-card);position:relative;">
                    <input type="radio" name="site_type" value="{{ $typeKey }}" {{ (old('site_type', $tenant->site_type ?? '') === $typeKey) ? 'checked' : '' }} style="position:absolute;top:12px;right:12px;width:16px;height:16px;accent-color:{{ $typeColors[$typeKey] ?? 'var(--accent-teal)' }};">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <div style="width:38px;height:38px;border-radius:8px;background:{{ $typeColors[$typeKey] ?? 'var(--accent-teal)' }}15;display:flex;align-items:center;justify-content:center;">
                            <i class="ti {{ $typeIcons[$typeKey] ?? 'ti-building' }}" style="font-size:18px;color:{{ $typeColors[$typeKey] ?? 'var(--accent-teal)' }};"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:13px;">{{ $type['label'] }}</div>
                            <div style="font-size:10px;color:var(--text-dim);">{{ count($type['default_modules'] ?? []) }} modules &middot; {{ $themeCount }} themes</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                        @foreach (array_slice($type['default_modules'] ?? [], 0, 4) as $mKey)
                            @if (isset($modules[$mKey]))
                                <span style="font-size:9px;padding:2px 6px;border-radius:4px;background:var(--bg-hover);color:var(--text-secondary);">{{ $modules[$mKey]['label'] }}</span>
                            @endif
                        @endforeach
                        @if (count($type['default_modules'] ?? []) > 4)
                            <span style="font-size:9px;padding:2px 6px;border-radius:4px;background:var(--bg-hover);color:var(--text-dim);">+{{ count($type['default_modules'] ?? []) - 4 }}</span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>
    </div>
</div>

{{-- HIDDEN site_type input for form submission --}}
<input type="hidden" name="site_type" id="siteTypeHidden" value="{{ old('site_type', $tenant->site_type ?? '') }}">

{{-- BOTTOM ACTIONS --}}
<div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
    @if (!$tenant)
        <button type="submit" class="eos-btn eos-btn-primary" style="padding:10px 24px;font-size:14px;"><i class="ti ti-check"></i> Create Tenant</button>
    @else
        <button type="submit" class="eos-btn eos-btn-primary" style="padding:10px 24px;font-size:14px;"><i class="ti ti-device-floppy"></i> Save Changes</button>
    @endif
    <a href="{{ route('tenants.index') }}" class="eos-btn eos-btn-secondary" style="padding:10px 24px;font-size:14px;">Cancel</a>
</div>

</form>

@if ($tenant)
    <div style="margin-top:12px;">
        <form method="POST" action="{{ route('tenants.toggle-status', $tenant) }}" style="display:inline;">
            @csrf
            <button type="submit" class="eos-btn {{ $tenant->status === 'active' ? 'eos-btn-danger' : 'eos-btn-primary' }}" style="font-size:12px;padding:6px 12px;">
                {{ $tenant->status === 'active' ? 'Suspend' : 'Activate' }}
            </button>
        </form>
    </div>
@endif

@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const siteTypeHidden = document.getElementById('siteTypeHidden');
    const typeCards = document.querySelectorAll('.business-type-card');

    function updateTypeCards(selectedType) {
        typeCards.forEach(card => {
            if (card.dataset.type === selectedType) {
                card.style.borderColor = '{{ $typeColors[$typeKey] ?? "var(--accent-teal)" }}';
            } else {
                card.style.borderColor = 'var(--border)';
            }
        });
    }

    typeCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type=radio][name=site_type]');
            if (radio) {
                radio.checked = true;
                siteTypeHidden.value = radio.value;
                updateTypeCards(radio.value);
            }
        });
    });

    document.querySelectorAll('.business-type-card input[type=radio][name=site_type]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                siteTypeHidden.value = this.value;
                updateTypeCards(this.value);
            }
        });
    });

    const actionTypeSelect = document.getElementById('actionTypeSelect');
    const customFields = document.querySelectorAll('.custom-gateway-fields');
    function toggleCustomGatewayFields() {
        const show = actionTypeSelect.value === 'custom';
        customFields.forEach(f => f.style.display = show ? 'block' : 'none');
    }
    actionTypeSelect.addEventListener('change', toggleCustomGatewayFields);
    toggleCustomGatewayFields();

    const checkedRadio = document.querySelector('.business-type-card input[type=radio][name=site_type]:checked');
    if (checkedRadio) {
        siteTypeHidden.value = checkedRadio.value;
        updateTypeCards(checkedRadio.value);
    }
});
</script>