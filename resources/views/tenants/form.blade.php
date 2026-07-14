@extends('layouts.app')

@section('title', $tenant ? 'Edit Tenant' : 'New Tenant')
@section('subtitle', $tenant ? 'Update tenant settings' : 'Create a new tenant site')

@section('content')
<div class="eos-row" style="gap:20px;">
    {{-- LEFT: Basic Info + Payment --}}
    <div class="eos-card" style="flex:1;min-width:360px;max-width:480px;">
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
                <label class="eos-label">Template</label>
                <select name="template_id" class="eos-input" id="templateSelect">
                    <option value="">Auto-assign by type</option>
                    @foreach ($themes as $key => $theme)
                        <option value="{{ $key }}" data-industries="{{ implode(',', $theme['industries'] ?? []) }}" {{ (old('template_id', $tenant->template_id ?? '') === $key) ? 'selected' : '' }}>
                            {{ $theme['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="eos-field">
                <label class="eos-label">Plan (legacy free-text)</label>
                <input type="text" name="plan" value="{{ old('plan', $tenant->plan ?? '') }}" class="eos-input" placeholder="e.g. Pro, Enterprise">
            </div>

            <div class="eos-field">
                <label class="eos-label">Payment Mode</label>
                <select name="action_type" class="eos-input" id="actionTypeSelect">
                    <option value="offline" {{ (old('action_type', $tenant->action_type ?? 'offline') === 'offline') ? 'selected' : '' }}>Offline / Manual</option>
                    <option value="razorpay" {{ (old('action_type', $tenant->action_type ?? '') === 'razorpay') ? 'selected' : '' }}>Razorpay (Online)</option>
                </select>
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

    {{-- RIGHT: Modules + Themes --}}
    <div style="flex:1;min-width:360px;display:flex;flex-direction:column;gap:16px;">

        {{-- MODULES CARD --}}
        <div class="eos-card" style="flex:1;">
            <div class="eos-card-header" style="display:flex;align-items:center;justify-content:space-between;">
                <div class="eos-card-title">Business Modules</div>
                <span class="eos-card-link" style="font-size:11px;">Toggles per business type</span>
            </div>
            <div class="eos-card-body" style="padding:16px;">
                <input type="hidden" name="modules" value="">
                <div id="modulesContainer" style="display:grid;grid-template-columns:1fr;gap:12px;">
                    @foreach ($businessTypes as $typeKey => $type)
                        <div class="eos-card module-card" data-type="{{ $typeKey }}" style="padding:12px;background:var(--bg-hover);border-radius:8px;border:1px solid var(--border);">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                <div style="width:36px;height:36px;border-radius:8px;background:var(--accent-teal-alpha,#d1fae5);display:flex;align-items:center;justify-content:center;">
                                    <i class="ti {{ $typeKey === 'shopping' ? 'ti-shopping-cart' : ($typeKey === 'restaurant' ? 'ti-utensils' : ($typeKey === 'business' ? 'ti-briefcase-2' : 'ti-info-circle')) }}" style="font-size:16px;color:var(--accent-teal);"></i>
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:13px;">{{ $type['label'] }}</div>
                                    <div style="font-size:11px;color:var(--text-dim);">{{ $type['default_modules'] ? implode(', ', $type['default_modules']) : 'No default modules' }}</div>
                                </div>
                            </div>
                            <div class="module-toggles" style="display:flex;flex-wrap:wrap;gap:8px;">
                                @php
                                    $typeModules = [];
                                    foreach ($modules as $mKey => $m) {
                                        if (in_array($mKey, $type['default_modules'] ?? [])) {
                                            $typeModules[$mKey] = $m;
                                        }
                                    }
                                @endphp
                                @foreach ($typeModules as $mKey => $m)
                                    <label style="display:flex;align-items:center;gap:6px;background:var(--bg-card);padding:6px 10px;border-radius:6px;border:1px solid var(--border);cursor:pointer;font-size:12px;">
                                        <input type="checkbox" name="modules[]" value="{{ $mKey }}" {{ in_array($mKey, old('modules', $tenant->modules ?? [])) ? 'checked' : '' }} style="width:14px;height:14px;accent-color:var(--accent-teal);">
                                        <span style="display:flex;align-items:center;gap:5px;">
                                            <i class="ti {{ $m['icon'] }}" style="font-size:12px;"></i>
                                            {{ $m['label'] }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- THEMES CARD --}}
        <div class="eos-card" style="flex:1;">
            <div class="eos-card-header">
                <div class="eos-card-title">Theme Template</div>
            </div>
            <div class="eos-card-body" style="padding:16px;">
                <div id="themesContainer" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
                    @foreach ($themes as $key => $theme)
                        @php $industries = $theme['industries'] ?? []; @endphp
                        <div class="theme-card" data-industries="{{ implode(',', $industries) }}" style="border:1px solid var(--border);border-radius:8px;overflow:hidden;background:var(--bg-card);cursor:pointer;transition:all .2s;" onclick="selectTheme(this, '{{ $key }}')">
                            <div style="aspect-ratio:16/10;background:var(--bg-hover);display:flex;align-items:center;justify-content:center;">
                                <i class="ti {{ $industries[0] === 'shopping' ? 'ti-shopping-cart' : ($industries[0] === 'info' ? 'ti-info-circle' : 'ti-palette') }}" style="font-size:32px;color:var(--accent-teal);"></i>
                            </div>
                            <div style="padding:10px;">
                                <div style="font-weight:600;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $theme['name'] }}</div>
                                <div style="font-size:10px;color:var(--text-dim);margin-top:2px;">{{ $theme['description'] ?? '' }}</div>
                                <div style="display:flex;gap:4px;margin-top:6px;flex-wrap:wrap;">
                                    @foreach ($industries as $ind)
                                        <span style="font-size:9px;background:var(--accent-teal-alpha,#d1fae5);color:var(--accent-teal);padding:2px 6px;border-radius:4px;">{{ $ind }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <input type="radio" name="template_id" value="{{ $key }}" style="display:none;" {{ (old('template_id', $tenant->template_id ?? '') === $key) ? 'checked' : '' }}>
                        </div>
                    @endforeach
                </div>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const siteTypeSelect = document.getElementById('siteTypeSelect');
    const moduleCards = document.querySelectorAll('.module-card');
    const themeCards = document.querySelectorAll('.theme-card');
    const templateSelect = document.getElementById('templateSelect');

    // Show/hide module cards based on business type
    function updateModuleCards() {
        const selectedType = siteTypeSelect.value;
        moduleCards.forEach(card => {
            if (card.dataset.type === selectedType) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        // Also filter themes
        filterThemes(selectedType);
    }

    function filterThemes(type) {
        themeCards.forEach(card => {
            const industries = card.dataset.industries.split(',');
            if (industries.includes(type) || type === '') {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function selectTheme(el, key) {
        document.querySelectorAll('.theme-card').forEach(c => c.style.borderColor = 'var(--border)');
        el.style.borderColor = 'var(--accent-teal)';
        el.style.borderWidth = '2px';
        el.querySelector('input[type=radio]').checked = true;
    }

    siteTypeSelect.addEventListener('change', updateModuleCards);

    // Initialize
    updateModuleCards();

    // Also sync with template select dropdown (legacy)
    if (templateSelect) {
        templateSelect.addEventListener('change', function() {
            const key = this.value;
            document.querySelectorAll('.theme-card').forEach(c => {
                if (c.dataset.industries.includes(key)) {
                    selectTheme(c, key);
                }
            });
        });
    }

    // Also listen for template_id change from radio
    document.querySelectorAll('input[name="template_id"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (templateSelect) templateSelect.value = this.value;
        });
    });
});
</script>
@endsection