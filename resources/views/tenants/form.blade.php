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
                <label class="eos-label">Client</label>
                <select name="client_id" class="eos-input" id="clientIdSelect">
                    <option value="">— No client (standalone) —</option>
                    @foreach ($clients as $c)
                        <option value="{{ $c->id }}" {{ (old('client_id', $prefillClient?->id ?? $tenant->client_id ?? '') == $c->id) ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
                <div style="font-size:10px;color:var(--text-dim);margin-top:3px;">Link this site to an existing client</div>
            </div>

            <div class="eos-field">
                <label class="eos-label">Site Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $tenant->name ?? ($prefillClient?->business_name ?? $prefillClient?->name ?? '')) }}" class="eos-input" required>
                @error('name') <div class="eos-error">{{ $message }}</div> @enderror
            </div>

            <div class="eos-field">
                <label class="eos-label">Business Type <span class="text-red-500">*</span></label>
                <div style="font-size:12px;color:var(--text-dim);padding:6px 0;" id="siteTypeLabel">Select a type from the cards →</div>
                @error('site_type') <div class="eos-error">{{ $message }}</div> @enderror
            </div>

            <div class="eos-field">
                <label class="eos-label">Site Delivery Mode <span class="text-red-500">*</span></label>
                <select name="site_mode" class="eos-input">
                    <option value="managed" @selected(old('site_mode', $tenant->site_mode ?? 'managed') === 'managed')>Managed content dashboard</option>
                    <option value="static" @selected(old('site_mode', $tenant->site_mode ?? 'managed') === 'static')>Static approved theme only</option>
                </select>
                <div style="font-size:10px;color:var(--text-dim);margin-top:4px;">Managed clients edit website content. Static clients use an admin-assigned approved HTML theme and receive only account, billing, domain, and support tools.</div>
            </div>

            <div class="eos-field">
                <label class="eos-label">Plan</label>
                <input type="text" name="plan" value="{{ old('plan', $tenant->plan ?? '') }}" class="eos-input" placeholder="e.g. Pro, Enterprise">
            </div>

            <div class="eos-field" style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px;">
                <label class="eos-label">Assigned Storefront Theme</label>
                <select name="template_id" class="eos-input">
                    <option value="">Auto default for business type</option>
                    @foreach ($themes as $themeKey => $theme)
                        <option value="{{ $themeKey }}" {{ old('template_id', $tenant->template_id ?? '') === $themeKey ? 'selected' : '' }}>
                            {{ $theme['name'] ?? $themeKey }}
                            @if (!empty($theme['industries']))
                                — {{ collect($theme['industries'])->map(fn($type) => $businessTypes[$type]['label'] ?? $type)->join(', ') }}
                            @endif
                        </option>
                    @endforeach
                </select>
                <div style="font-size:10px;color:var(--text-dim);margin-top:4px;">Static delivery requires a real installed theme created from pasted HTML or an uploaded Theme Kit. Managed delivery can use any compatible installed theme.</div>
            </div>

            <div class="eos-field">
                <label class="eos-label">Hosting Product</label>
                <select name="hosting_plan_id" class="eos-input">
                    <option value="">No hosting product assigned</option>
                    @foreach ($hostingPlans as $hostingPlan)
                        <option value="{{ $hostingPlan->id }}" {{ (string) old('hosting_plan_id', $tenant->hosting_plan_id ?? '') === (string) $hostingPlan->id ? 'selected' : '' }}>
                            {{ $hostingPlan->name }} — ₹{{ number_format($hostingPlan->price, 0) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="eos-field">
                <label class="eos-label">Custom Domain</label>
                <input type="text" name="custom_domain" value="{{ old('custom_domain', $tenant->custom_domain ?? '') }}" class="eos-input" placeholder="www.clientdomain.com">
                <div style="font-size:10px;color:var(--text-dim);margin-top:4px;">Leave empty if this tenant only uses the Ehlom subdomain.</div>
            </div>

            <div class="eos-field">
                <label class="eos-label">Domain Status</label>
                <select name="domain_status" class="eos-input">
                    @foreach (['none' => 'No custom domain', 'pending' => 'Pending verification', 'verified' => 'Verified / Live'] as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" {{ old('domain_status', $tenant->domain_status ?? 'none') === $statusKey ? 'selected' : '' }}>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="eos-field">
                <label class="eos-label">Default Checkout Action</label>
                <select name="action_type" class="eos-input">
                    @foreach (['whatsapp' => 'WhatsApp Order', 'offline' => 'Cash on Delivery / Offline', 'razorpay' => 'Razorpay', 'stripe' => 'Stripe', 'paypal' => 'PayPal', 'custom' => 'Custom Gateway'] as $actionKey => $actionLabel)
                        <option value="{{ $actionKey }}" {{ old('action_type', $tenant->action_type ?? 'whatsapp') === $actionKey ? 'selected' : '' }}>{{ $actionLabel }}</option>
                    @endforeach
                </select>
            </div>

            @if (!$tenant)
                <div class="eos-field" style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px;">
                    <label class="eos-label">Owner Name <span class="text-red-500">*</span></label>
                    <input type="text" name="owner_name" value="{{ old('owner_name', $prefillClient?->name ?? '') }}" class="eos-input" required>
                    @error('owner_name') <div class="eos-error">{{ $message }}</div> @enderror
                </div>

                <div class="eos-field">
                    <label class="eos-label">Owner Email <span class="text-red-500">*</span></label>
                    <input type="email" name="owner_email" value="{{ old('owner_email', $prefillClient?->email ?? '') }}" class="eos-input" required>
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
                <label class="business-type-card" data-type="{{ $typeKey }}" data-color="{{ $typeColors[$typeKey] ?? '#10b981' }}" style="display:block;border:2px solid var(--border);border-radius:10px;padding:16px;cursor:pointer;transition:all .2s;background:var(--bg-card);position:relative;">
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

        <div class="eos-card" style="margin-top:16px;">
            <div class="eos-card-header">
                <div>
                    <div class="eos-card-title">Assigned Client Features</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">These toggles control what appears inside the client dashboard and storefront for this tenant.</div>
                </div>
            </div>
            <div class="eos-card-body" style="padding:14px;">
                @php
                    $selectedModules = old('modules', $tenant->modules ?? []);
                    $priceLabel = function ($moduleKey, $module, $typeKey) use ($moduleAssignments) {
                        $assignment = $moduleAssignments[$typeKey][$moduleKey] ?? null;

                        if (($assignment['status'] ?? null) !== 'paid' && !empty($module['free'])) {
                            return 'Free';
                        }

                        $price = $assignment['price'] ?? $module['price'] ?? 0;
                        $cycle = $assignment['billing_cycle'] ?? 'one_time';
                        $suffix = $cycle === 'one_time' ? 'once' : '/' . ['monthly' => 'month', 'quarterly' => 'quarter', 'yearly' => 'year'][$cycle];

                        return '₹' . number_format((float) $price, 0) . ' ' . $suffix;
                    };
                    $moduleGroups = collect($modules)
                        ->reject(fn($module, $key) => $key === 'bundles' || !is_array($module) || empty($module['business_types']))
                        ->groupBy(fn($module) => $module['business_types'][0] ?? 'other');
                @endphp
                @foreach ($businessTypes as $typeKey => $type)
                    @php $typeModules = $moduleGroups->get($typeKey, collect()); @endphp
                    @if ($typeModules->count())
                        <div class="tenant-module-group" data-module-type="{{ $typeKey }}" style="margin-bottom:14px;">
                            <div style="font-size:11px;font-weight:800;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">{{ $type['label'] }}</div>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:8px;">
                                @foreach ($typeModules as $moduleKey => $module)
                                    @php
                                        $isChecked = in_array($moduleKey, $selectedModules ?? [], true) || ($tenant && $tenant->hasModule($moduleKey));
                                        $assignment = $moduleAssignments[$typeKey][$moduleKey] ?? null;
                                        $isPaid = ($assignment['status'] ?? null) === 'paid' || (((float) ($module['price'] ?? 0)) > 0 && empty($module['free']));
                                    @endphp
                                    <label style="display:flex;align-items:flex-start;gap:8px;border:1px solid var(--border);border-radius:9px;padding:9px;background:var(--bg-card);cursor:pointer;">
                                        <input type="checkbox" name="modules[]" value="{{ $moduleKey }}" {{ $isChecked ? 'checked' : '' }} style="margin-top:2px;accent-color:var(--accent-blue);">
                                        <span style="min-width:0;">
                                            <span style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:12px;font-weight:800;color:var(--text-primary);">
                                                {{ $module['label'] ?? $moduleKey }}
                                                <span style="font-size:9px;padding:1px 6px;border-radius:999px;background:{{ $isPaid ? '#fffbeb' : '#ecfdf5' }};color:{{ $isPaid ? '#d97706' : '#059669' }};font-weight:800;">{{ $priceLabel($moduleKey, $module, $typeKey) }}</span>
                                            </span>
                                            <span style="display:block;font-size:10px;color:var(--text-dim);line-height:1.4;">{{ $module['description'] ?? '' }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeCards = document.querySelectorAll('.business-type-card');

    function updateTypeCards(selectedType) {
        const label = document.getElementById('siteTypeLabel');
        typeCards.forEach(card => {
            if (card.dataset.type === selectedType) {
                card.style.borderColor = card.dataset.color || 'var(--accent-teal)';
                card.style.background = (card.dataset.color || '#10b981') + '0a';
                if (label) label.textContent = card.querySelector('div > div > div:first-child')?.textContent || '';
            } else {
                card.style.borderColor = 'var(--border)';
                card.style.background = 'var(--bg-card)';
            }
        });

        document.querySelectorAll('.tenant-module-group').forEach(group => {
            const active = group.dataset.moduleType === selectedType;
            group.style.display = active ? 'block' : 'none';
            group.querySelectorAll('input[type=checkbox]').forEach(input => {
                input.disabled = !active;
            });
        });
    }

    typeCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type=radio][name=site_type]');
            if (radio) {
                radio.checked = true;
                updateTypeCards(radio.value);
            }
        });
    });

    document.querySelectorAll('.business-type-card input[type=radio][name=site_type]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                updateTypeCards(this.value);
            }
        });
    });

    const checkedRadio = document.querySelector('.business-type-card input[type=radio][name=site_type]:checked');
    if (checkedRadio) {
        updateTypeCards(checkedRadio.value);
    }

    const clientIdSelect = document.getElementById('clientIdSelect');
    const clientsData = @json($clients->keyBy('id'));
    if (clientIdSelect) {
        clientIdSelect.addEventListener('change', function() {
            const client = clientsData[this.value];
            if (!client) return;
            const subdomainInput = document.querySelector('input[name=subdomain]');
            if (subdomainInput && !subdomainInput.value) {
                subdomainInput.value = client.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
    }
});
</script>

@endsection
