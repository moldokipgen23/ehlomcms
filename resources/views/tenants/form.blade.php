@extends('layouts.app')

@section('title', $tenant ? 'Edit Tenant' : 'New Tenant')
@section('subtitle', $tenant ? 'Update tenant settings' : 'Create a new tenant site')

@section('content')
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
                <input type="text" name="name" value="{{ old('name', $tenant->name ?? '') }}" class="eos-input" required>
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
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" class="eos-input" required>
                    @error('owner_name') <div class="eos-error">{{ $message }}</div> @enderror
                </div>

                <div class="eos-field">
                    <label class="eos-label">Owner Email <span class="text-red-500">*</span></label>
                    <input type="email" name="owner_email" value="{{ old('owner_email') }}" class="eos-input" required>
                    @error('owner_email') <div class="eos-error">{{ $message }}</div> @enderror
                </div>
            @endif

            @if ($tenant)
                <div style="border-top:1px solid var(--border);padding-top:12px;">
                    <form method="POST" action="{{ route('tenants.toggle-status', $tenant) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="eos-btn {{ $tenant->status === 'active' ? 'eos-btn-danger' : 'eos-btn-primary' }}" style="font-size:12px;padding:6px 12px;">
                            {{ $tenant->status === 'active' ? 'Suspend' : 'Activate' }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- RIGHT: Business Type Cards (Modules + Themes per type) --}}
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:16px;">

        @foreach ($businessTypes as $typeKey => $type)
            <div class="eos-card business-type-card" data-type="{{ $typeKey }}" style="display:none;">
                <div class="eos-card-header" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg-hover);border-bottom:1px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:var(--accent-teal-alpha,#d1fae5);display:flex;align-items:center;justify-content:center;">
                            <i class="ti {{ $typeKey === 'shopping' ? 'ti-shopping-cart' : ($typeKey === 'restaurant' ? 'ti-utensils' : ($typeKey === 'business' ? 'ti-briefcase-2' : 'ti-info-circle')) }}" style="font-size:16px;color:var(--accent-teal);"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:14px;">{{ $type['label'] }}</div>
                            <div style="font-size:11px;color:var(--text-dim);">Modules & Themes for this type</div>
                        </div>
                    </div>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-secondary);cursor:pointer;">
                        <input type="radio" name="site_type" value="{{ $typeKey }}" {{ (old('site_type', $tenant->site_type ?? '') === $typeKey) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:var(--accent-teal);" onchange="this.form.site_type.value=this.value;updateTypeCards(this.value);">
                        <span style="font-size:12px;">Select this type</span>
                    </label>
                </div>

                <div class="eos-card-body" style="padding:16px;display:flex;flex-direction:column;gap:16px;">

                    {{-- MODULES: Free vs Paid toggles --}}
                    <div>
                        <div style="font-weight:600;font-size:12px;color:var(--text-secondary);margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                            <i class="ti ti-box" style="font-size:14px;color:var(--accent-teal);"></i> Modules
                        </div>
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            {{-- FREE MODULES --}}
                            @php
                                $typeModules = [];
                                foreach ($modules as $mKey => $m) {
                                    if (in_array($mKey, $type['default_modules'] ?? [])) {
                                        $typeModules[$mKey] = $m;
                                    }
                                }
                                $freeModules = [];
                                $paidModules = [];
                                foreach ($typeModules as $mKey => $m) {
                                    if (!empty($m['free'])) {
                                        $freeModules[$mKey] = $m;
                                    } else {
                                        $paidModules[$mKey] = $m;
                                    }
                                }
                            @endphp

                            @if (!empty($freeModules))
                                <div style="margin-bottom:12px;">
                                    <div style="font-size:10px;color:var(--accent-teal);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">
                                        <i class="ti ti-gift" style="font-size:10px;"></i> Free Modules
                                    </div>
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                        @foreach ($freeModules as $mKey => $m)
                                            <label style="display:flex;align-items:center;gap:6px;background:var(--bg-card);padding:6px 10px;border-radius:6px;border:1px solid var(--border);cursor:pointer;font-size:12px;">
                                                <input type="checkbox" name="modules[]" value="{{ $mKey }}" {{ in_array($mKey, old('modules', $tenant->modules ?? [])) ? 'checked' : '' }} style="width:14px;height:14px;accent-color:var(--accent-teal);">
                                                <span style="display:flex;align-items:center;gap:5px;">
                                                    <i class="ti {{ $m['icon'] }}" style="font-size:12px;"></i>
                                                    {{ $m['label'] }}
                                                    <span style="font-size:9px;background:var(--accent-teal-alpha,#d1fae5);color:var(--accent-teal);padding:1px 4px;border-radius:3px;">FREE</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- PAID MODULES --}}
                            @if (!empty($paidModules))
                                <div>
                                    <div style="font-size:10px;color:var(--accent-amber);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">
                                        <i class="ti ti-coin" style="font-size:10px;"></i> Paid Add-ons
                                    </div>
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                        @foreach ($paidModules as $mKey => $m)
                                            @php $price = $m['price'] ?? 0; @endphp
                                            <label style="display:flex;align-items:center;gap:6px;background:var(--bg-card);padding:6px 10px;border-radius:6px;border:1px solid var(--border);cursor:pointer;font-size:12px;">
                                                <input type="checkbox" name="modules[]" value="{{ $mKey }}" {{ in_array($mKey, old('modules', $tenant->modules ?? [])) ? 'checked' : '' }} style="width:14px;height:14px;accent-color:var(--accent-teal);">
                                                <span style="display:flex;align-items:center;gap:5px;">
                                                    <i class="ti {{ $m['icon'] }}" style="font-size:12px;"></i>
                                                    {{ $m['label'] }}
                                                    <span style="font-size:9px;background:var(--accent-amber-alpha,#fef3c7);color:var(--accent-amber);padding:1px 4px;border-radius:3px;">₹{{ number_format($price, 0) }}/mo</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (empty($typeModules))
                                <span style="font-size:11px;color:var(--text-dim);">No modules configured for this type</span>
                            @endif
                        </div>
                    </div>

                    {{-- THEMES FOR THIS BUSINESS TYPE --}}
                    <div>
                        <div style="font-weight:600;font-size:12px;color:var(--text-secondary);margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                            <i class="ti ti-palette" style="font-size:14px;color:var(--accent-teal);"></i> Theme Template
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
                            @php
                                $typeThemes = [];
                                foreach ($themes as $key => $theme) {
                                    $industries = $theme['industries'] ?? [];
                                    if (in_array($typeKey, $industries)) {
                                        $typeThemes[$key] = $theme;
                                    }
                                }
                            @endphp
                            @foreach ($typeThemes as $key => $theme)
                                @php $industries = $theme['industries'] ?? []; @endphp
                                <div class="theme-card" style="border:1px solid var(--border);border-radius:8px;overflow:hidden;background:var(--bg-card);cursor:pointer;transition:all .2s;" onclick="selectTheme(this, '{{ $key }}')">
                                    <div style="aspect-ratio:16/10;background:var(--bg-hover);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti {{ $industries[0] === 'shopping' ? 'ti-shopping-cart' : ($industries[0] === 'info' ? 'ti-info-circle' : 'ti-palette') }}" style="font-size:28px;color:var(--accent-teal);"></i>
                                    </div>
                                    <div style="padding:8px;">
                                        <div style="font-weight:600;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $theme['name'] }}</div>
                                        <div style="font-size:9px;color:var(--text-dim);margin-top:2px;">{{ $theme['description'] ?? '' }}</div>
                                        <input type="radio" name="template_id" value="{{ $key }}" style="display:none;" {{ (old('template_id', $tenant->template_id ?? '') === $key) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            @endforeach
                            @if (empty($typeThemes))
                                <span style="font-size:11px;color:var(--text-dim);">No themes configured for this type</span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        @endforeach

        {{-- FALLBACK: All Themes Card --}}
        <div class="eos-card business-type-card" data-type="" style="display:none;">
            <div class="eos-card-header" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg-hover);border-bottom:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:var(--bg-card);display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-palette" style="font-size:16px;color:var(--text-muted);"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:14px;">All Themes</div>
                        <div style="font-size:11px;color:var(--text-dim);">Fallback themes</div>
                    </div>
                </div>
                <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-secondary);cursor:pointer;">
                    <input type="radio" name="site_type" value="" style="width:16px;height:16px;accent-color:var(--accent-teal);" onchange="this.form.site_type.value=this.value;updateTypeCards(this.value);">
                    <span style="font-size:12px;">Select</span>
                </label>
            </div>
            <div class="eos-card-body" style="padding:16px;">
                <div style="font-weight:600;font-size:12px;color:var(--text-secondary);margin-bottom:8px;">All Available Themes</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
                    @foreach ($themes as $key => $theme)
                        @php $industries = $theme['industries'] ?? []; @endphp
                        <div class="theme-card" style="border:1px solid var(--border);border-radius:8px;overflow:hidden;background:var(--bg-card);cursor:pointer;transition:all .2s;" onclick="selectTheme(this, '{{ $key }}')">
                            <div style="aspect-ratio:16/10;background:var(--bg-hover);display:flex;align-items:center;justify-content:center;">
                                <i class="ti {{ $industries[0] === 'shopping' ? 'ti-shopping-cart' : ($industries[0] === 'info' ? 'ti-info-circle' : 'ti-palette') }}" style="font-size:28px;color:var(--accent-teal);"></i>
                            </div>
                            <div style="padding:8px;">
                                <div style="font-weight:600;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $theme['name'] }}</div>
                                <div style="font-size:9px;color:var(--text-dim);margin-top:2px;">{{ $theme['description'] ?? '' }}</div>
                                <input type="radio" name="template_id" value="{{ $key }}" style="display:none;" {{ (old('template_id', $tenant->template_id ?? '') === $key) ? 'checked' : '' }}>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const siteTypeSelect = document.getElementById('siteTypeSelect');
    const siteTypeHidden = document.getElementById('siteTypeHidden');
    const typeCards = document.querySelectorAll('.business-type-card');

    // Show/hide type cards based on dropdown selection
    function updateTypeCards(selectedType) {
        typeCards.forEach(card => {
            if (card.dataset.type === selectedType || (selectedType === '' && card.dataset.type === '')) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Sync dropdown with hidden input
    siteTypeSelect.addEventListener('change', function() {
        siteTypeHidden.value = this.value;
        updateTypeCards(this.value);
    });

    // Radio buttons inside cards also update dropdown
    document.querySelectorAll('.business-type-card input[type=radio][name=site_type]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                siteTypeSelect.value = this.value;
                siteTypeHidden.value = this.value;
                updateTypeCards(this.value);
            }
        });
    });

    // Theme selection
    function selectTheme(el, key) {
        document.querySelectorAll('.theme-card').forEach(c => c.style.borderColor = 'var(--border)');
        el.style.borderColor = 'var(--accent-teal)';
        el.style.borderWidth = '2px';
        el.querySelector('input[type=radio]').checked = true;
    }

    // Custom Gateway toggle
    const actionTypeSelect = document.getElementById('actionTypeSelect');
    const customFields = document.querySelectorAll('.custom-gateway-fields');
    function toggleCustomGatewayFields() {
        const show = actionTypeSelect.value === 'custom';
        customFields.forEach(f => f.style.display = show ? 'block' : 'none');
    }
    actionTypeSelect.addEventListener('change', toggleCustomGatewayFields);
    toggleCustomGatewayFields();

    // Initialize
    updateTypeCards(siteTypeSelect.value);
});
</script>
@endsection