@extends('layouts.app')

@section('title', 'Tenants')

@section('subtitle', 'All client tenant sites')

@section('content')

@if (session('generated_login'))
    @php $gl = session('generated_login'); @endphp
    <div class="eos-card" style="margin-bottom:16px;border-color:var(--accent-teal);">
        <div style="padding:16px;">
            <div style="font-weight:700;color:var(--accent-teal);margin-bottom:8px;">
                <i class="ti ti-key"></i> Owner login created — shown once, save it now
            </div>
            <div style="font-size:13px;color:var(--text-secondary);line-height:1.8;">
                Site: <strong>{{ $gl['subdomain'] }}.{{ config('app.tenant_domain', 'ehlom.com') }}/dashboard/login</strong><br>
                Email: <strong>{{ $gl['email'] }}</strong><br>
                Password: <strong>{{ $gl['password'] }}</strong>
            </div>
            <div class="eos-page-sub" style="margin-top:8px;">
                Pass these to the client yourself. This won't be shown again — the
                password is hashed in the database, not stored in plaintext anywhere.
            </div>
        </div>
    </div>
@endif

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        {{ $tenants->count() }} Tenant{{ $tenants->count() !== 1 ? 's' : '' }}
    </div>
    <a href="{{ route('tenants.create') }}" class="eos-btn eos-btn-primary">
        <i class="ti ti-plus"></i> New Tenant
    </a>
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Subdomain</th>
            <th>Name</th>
            <th>Site Type</th>
            <th>Template</th>
            <th>Hosting Plan</th>
            <th>Status</th>
            <th>Add-ons</th>
            <th>Client</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tenants as $tenant)
            <tr class="{{ $tenant->status === 'suspended' ? 'eos-tr-warn' : '' }}">
                <td>
                    <a href="{{ request()->getScheme() }}://{{ $tenant->subdomain }}.{{ config('app.tenant_domain', 'ehlom.com') }}/" target="_blank" rel="noopener" style="font-size:11px;">
                        {{ $tenant->subdomain }}
                    </a>
                </td>
                <td style="font-weight:600;">{{ $tenant->name }}</td>
                <td><span class="eos-badge {{ $tenant->site_type === 'shopping' ? 'badge-paid' : 'badge-draft' }}">{{ $tenant->site_type ?? '—' }}</span></td>
                <td>{{ $tenant->template_id ?? '—' }}</td>
                <td>
                    <form method="POST" action="{{ route('tenants.hosting-plan', $tenant) }}">
                        @csrf
                        <select name="hosting_plan_id" onchange="this.form.submit()" class="eos-select" style="font-size:11px;padding:3px 6px;border-radius:5px;border:1px solid var(--border);background:var(--bg-card);color:var(--text-primary);cursor:pointer;">
                            <option value="">— None —</option>
                            @foreach ($hostingPlans as $plan)
                                <option value="{{ $plan->id }}" {{ $tenant->hosting_plan_id === $plan->id ? 'selected' : '' }}>{{ $plan->name }} (₹{{ number_format($plan->price, 0) }})</option>
                            @endforeach
                        </select>
                    </form>
                    @if ($tenant->plan)
                        <div style="font-size:10px;color:var(--text-dim);margin-top:2px;" title="Legacy free-text plan field, predates the Hosting Plans catalog">{{ $tenant->plan }}</div>
                    @endif
                </td>
                <td>
                    <span class="eos-badge {{ $tenant->status === 'active' ? 'badge-active' : ($tenant->status === 'suspended' ? 'badge-suspended' : 'badge-pending') }}">
                        {{ $tenant->status }}
                    </span>
                </td>
                <td style="font-size:11px;">
                    @php $addonNames = $tenant->activeAddons->map(fn($a) => config('addons.' . $a->addon_key . '.name', $a->addon_key)); @endphp
                    @if ($addonNames->count())
                        <span title="{{ $addonNames->implode(', ') }}">{{ $addonNames->implode(', ') }}</span>
                    @else
                        <span style="color:var(--text-dim);">—</span>
                    @endif
                </td>
                <td>
                    @if ($tenant->client)
                        <a href="{{ route('clients.show', $tenant->client) }}" style="color:var(--accent-blue);text-decoration:none;">{{ $tenant->client->name }}</a>
                    @else
                        <span style="color:var(--text-dim);">—</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('tenants.edit', $tenant) }}" class="eos-btn eos-btn-secondary" style="font-size:10px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;text-decoration:none;" title="Edit tenant">
                        <i class="ti ti-pencil"></i>
                    </a>
                    <form method="POST" action="{{ route('tenants.impersonate', $tenant) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="eos-btn eos-btn-secondary" style="font-size:10px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;" title="Login as this tenant">
                            <i class="ti ti-eye"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('tenants.toggle-status', $tenant) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="eos-btn eos-btn-{{ $tenant->status === 'active' ? 'danger' : 'primary' }}" style="font-size:10px;padding:4px 10px;">
                            {{ $tenant->status === 'active' ? 'Suspend' : 'Activate' }}
                        </button>
                    </form>
                    <details style="display:inline-block;position:relative;margin-left:4px;">
                        <summary class="eos-btn eos-btn-secondary" style="font-size:10px;padding:4px 10px;display:inline-flex;cursor:pointer;list-style:none;">
                            <i class="ti ti-copy"></i> Save as Template
                        </summary>
                        <form action="{{ route('tenants.save-as-template', $tenant) }}" method="POST"
                              style="position:absolute;right:0;z-index:10;background:var(--bg-card);border:1px solid var(--border-card);border-radius:8px;padding:12px;width:220px;margin-top:6px;">
                            @csrf
                            <label class="eos-label" style="font-size:10px;">Theme name</label>
                            <input type="text" name="name" class="eos-input" style="font-size:11px;padding:6px 8px;margin:4px 0 8px;" required placeholder="e.g. {{ $tenant->name }} Style">
                            <label style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-secondary);margin-bottom:8px;">
                                <input type="checkbox" name="public" value="1"> Make public
                            </label>
                            <button type="submit" class="eos-btn eos-btn-primary" style="font-size:10px;padding:5px 10px;width:100%;">Save</button>
                        </form>
                    </details>
                    <details style="display:inline-block;position:relative;margin-left:4px;">
                        <summary class="eos-btn eos-btn-primary" style="font-size:10px;padding:4px 10px;display:inline-flex;cursor:pointer;list-style:none;">
                            <i class="ti ti-gift"></i> Grant Add-on
                        </summary>
                        <form action="{{ route('admin.addons.grant', $tenant) }}" method="POST"
                              style="position:absolute;right:0;z-index:10;background:var(--bg-card);border:1px solid var(--border-card);border-radius:8px;padding:12px;width:280px;margin-top:6px;">
                            @csrf
                            <label class="eos-label" style="font-size:10px;">Add-on</label>
                            <select name="addon_key" class="eos-select" style="font-size:11px;padding:6px 8px;margin:4px 0 8px;width:100%;" required>
                                <option value="">Select add-on…</option>
                                @foreach (\App\Models\AddonProduct::where('active', true)->whereNull('module_key')->orderBy('name')->get() as $addon)
                                    <option value="{{ $addon->key }}">{{ $addon->name }} (₹{{ number_format($addon->price, 0) }}/{{ $addon->billingLabel() }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="eos-btn eos-btn-primary" style="font-size:10px;padding:5px 10px;width:100%;">Activate Free</button>
                        </form>
                    </details>
                </td>
            </tr>
        @empty
            <tr><td colspan="9"><div class="eos-empty">No tenants yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
