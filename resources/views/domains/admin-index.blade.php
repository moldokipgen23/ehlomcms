@extends('layouts.app')

@section('title', 'Custom Domains')
@section('subtitle', 'Manage tenant custom domain assignments')

@section('content')
<div style="font-size:11.5px;color:var(--text-secondary);background:var(--bg-hover);border-radius:8px;padding:12px;margin-bottom:16px;line-height:1.6;">
    <div style="font-weight:600;color:var(--text-primary);margin-bottom:6px;"><i class="ti ti-world"></i> Custom Domain Setup</div>
    <div style="margin-bottom:8px;">To point a custom domain to a tenant's site:</div>
    <ol style="margin:0;padding-left:18px;line-height:1.8;">
        <li>Log in to your domain registrar (GoDaddy, Namecheap, etc.)</li>
        <li>Go to DNS Management for the domain</li>
        <li>Add a <strong>CNAME Record</strong>:
            <ul style="margin:2px 0;padding-left:16px;">
                <li><strong>Host/Name:</strong> <code>@</code> (or the subdomain, e.g. <code>shop</code>)</li>
                <li><strong>Value/Target:</strong> <code id="cnameTarget">{{ config('app.tenant_domain', 'ehlom.com') }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ config('app.tenant_domain', 'ehlom.com') }}');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500)" style="font-size:10px;padding:2px 6px;border:1px solid var(--border);border-radius:4px;background:none;color:var(--accent-teal);cursor:pointer;margin-left:4px;">Copy</button>
                </li>
                <li><strong>TTL:</strong> <code>300</code> (5 minutes)</li>
            </ul>
        </li>
        <li>Wait 5-30 minutes for DNS propagation</li>
        <li>Click <strong>Verify</strong> next to the tenant below</li>
    </ol>
    <div style="margin-top:8px;font-size:10px;color:var(--text-dim);">After verification, click <strong>SSL</strong> to issue a certificate, then the site will be live on the custom domain.</div>
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Tenant</th>
            <th>Client</th>
            <th>Custom Domain</th>
            <th>Status</th>
            <th>Verified At</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tenants as $tenant)
            <tr>
                <td style="font-weight:600;">{{ $tenant->name }}</td>
                <td style="font-size:12px;">
                    @if ($tenant->client)
                        <a href="{{ route('clients.show', $tenant->client) }}" style="color:var(--accent-blue);text-decoration:none;">{{ $tenant->client->name }}</a>
                    @else
                        <span style="color:var(--text-dim);">—</span>
                    @endif
                </td>
                <td>
                    @if ($tenant->custom_domain)
                        <code>{{ $tenant->custom_domain }}</code>
                    @else
                        <form method="POST" action="{{ route('domains.admin.store', $tenant) }}" style="display:flex;gap:6px;">
                            @csrf
                            <input type="text" name="custom_domain" placeholder="shop.example.com" required
                                   style="font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--bg-hover);color:var(--text-primary);width:180px;">
                            <button type="submit" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;">Set</button>
                        </form>
                    @endif
                </td>
                <td>
                    <span class="eos-badge badge-{{ $tenant->domain_status === 'verified' ? 'active' : ($tenant->domain_status === 'pending' ? 'draft' : '') }}">
                        {{ $tenant->domain_status }}
                    </span>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $tenant->domain_verified_at?->format('M j, Y') ?? '—' }}</td>
                <td style="text-align:right;white-space:nowrap;">
                    @if ($tenant->custom_domain && $tenant->domain_status !== 'verified')
                        <form method="POST" action="{{ route('domains.verify', $tenant) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;">Verify</button>
                        </form>
                    @endif
                    @if ($tenant->custom_domain && $tenant->domain_status === 'verified')
                        <form method="POST" action="{{ route('domains.ssl-issue', $tenant) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid var(--accent);border-radius:6px;color:var(--accent);background:none;cursor:pointer;"><i class="ti ti-shield"></i> SSL</button>
                        </form>
                        <form method="POST" action="{{ route('domains.ssl-renew', $tenant) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid #f59e0b;border-radius:6px;color:#f59e0b;background:none;cursor:pointer;"><i class="ti ti-refresh"></i> Renew</button>
                        </form>
                    @endif
                    @if ($tenant->custom_domain)
                        <form method="POST" action="{{ route('domains.remove', $tenant) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid #ef4444;border-radius:6px;color:#ef4444;background:none;cursor:pointer;" onclick="return confirm('Remove custom domain?')">Remove</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="eos-empty">No custom domains configured.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
