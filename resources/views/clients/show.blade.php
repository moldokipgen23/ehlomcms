@extends('layouts.app')

@section('title', $client->name)
@section('subtitle', $client->business_name ?: 'Client profile')

@section('topbar-action')
    <a href="{{ route('clients.edit', $client) }}" class="eos-icon-btn"><i class="ti ti-pencil"></i> Edit</a>
@endsection

@section('content')
<div x-data="{ tab: 'details' }">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
        <div class="eos-init" style="width:44px;height:44px;font-size:15px;">{{ strtoupper(substr($client->name, 0, 2)) }}</div>
        <div>
            <div style="font-size:16px;font-weight:700;">{{ $client->name }}</div>
            <div style="font-size:11px;color:var(--text-dim);">{{ $client->phone }} @if($client->email)· {{ $client->email }}@endif</div>
        </div>
        <span class="eos-badge badge-{{ $client->status }}" style="margin-left:auto;">{{ strtoupper($client->status) }}</span>
    </div>

    <div class="eos-tabs">
        @foreach (['details' => 'Details', 'activity' => 'Activity', 'whatsapp' => 'WhatsApp', 'subscriptions' => 'Subscriptions', 'projects' => 'Projects', 'invoices' => 'Invoices', 'domains' => 'Domains & Hosting', 'agreements' => 'Agreements', 'notes' => 'Notes'] as $key => $label)
            <div class="eos-tab" :class="{ 'active': tab === '{{ $key }}' }" @click="tab = '{{ $key }}'">{{ $label }}</div>
        @endforeach
    </div>

    {{-- Details --}}
    <div x-show="tab === 'details'">
        <div class="eos-card" style="margin-bottom:14px;">
            <div class="eos-form-grid">
                @foreach ([
                    'Name' => $client->name,
                    'Business Name' => $client->business_name ?: '—',
                    'Phone' => $client->phone,
                    'WhatsApp' => $client->whatsapp ?: '—',
                    'Email' => $client->email ?: '—',
                    'Address' => $client->address ?: '—',
                ] as $label => $value)
                    <div>
                        <div class="eos-label">{{ $label }}</div>
                        <div style="font-size:12.5px;color:var(--text-secondary);">{{ $value }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Pending Invoices --}}
        @php
            $pending = $client->invoices->whereIn('status', ['unpaid', 'overdue'])->sortBy('due_date');
            $outstanding = $pending->sum('total');
        @endphp
        <div class="eos-card">
            <div class="eos-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                <div class="eos-card-title">Pending Invoices</div>
                @if ($pending->count())
                    <span class="eos-amt" style="font-size:14px;color:var(--accent-amber,#f59e0b);">
                        ₹{{ number_format($outstanding, 2) }} outstanding
                    </span>
                @endif
            </div>
            @if ($pending->count())
                <table class="eos-table">
                    <thead><tr><th>Invoice #</th><th>Total</th><th>Due</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($pending as $invoice)
                            @php
                                $overdue = $invoice->due_date && $invoice->due_date->isPast();
                                $rmMsg = "Hi {$client->name}, a friendly reminder from Ehlom Digital: invoice "
                                    . "{$invoice->invoice_number} for ₹" . number_format($invoice->total, 2)
                                    . ($invoice->due_date ? ' (due ' . $invoice->due_date->format('M j, Y') . ')' : '')
                                    . ' is still pending. Kindly arrange the payment. Thank you!';
                            @endphp
                            <tr>
                                <td><a href="{{ route('invoices.show', $invoice) }}" style="font-weight:600;color:var(--text-primary);">{{ $invoice->invoice_number }}</a></td>
                                <td>₹{{ number_format($invoice->total, 2) }}</td>
                                <td style="{{ $overdue ? 'color:#ef4444;font-weight:600;' : '' }}">{{ $invoice->due_date?->format('M j, Y') ?: '—' }}</td>
                                <td><span class="eos-badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></td>
                                <td>
                                    <div class="eos-actions" style="justify-content:flex-end;">
                                        @if ($client->whatsapp)
                                            <a href="{{ \App\Helpers\WhatsAppHelper::link($client->whatsapp, $rmMsg) }}" target="_blank" rel="noopener" class="eos-icon-action edit" title="Send WhatsApp reminder"><i class="ti ti-brand-whatsapp"></i></a>
                                        @endif
                                        @if ($client->email)
                                            <a href="mailto:{{ $client->email }}?subject={{ rawurlencode('Payment reminder — ' . $invoice->invoice_number) }}&body={{ rawurlencode($rmMsg) }}" class="eos-icon-action edit" title="Send email reminder"><i class="ti ti-mail"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="eos-empty">No pending invoices — all settled.</div>
            @endif
        </div>
    </div>

    {{-- Activity Timeline --}}
    <div x-show="tab === 'activity'" x-cloak>
        @php
            $types = ['note', 'call', 'email', 'invoice_sent', 'payment_received', 'hosting_renewed', 'domain_renewed', 'project_update', 'whatsapp', 'other'];
            $typeColor = [
                'call' => 'green', 'payment_received' => 'green', 'hosting_renewed' => 'green',
                'domain_renewed' => 'green', 'whatsapp' => 'green',
                'note' => 'blue', 'project_update' => 'blue',
                'email' => 'purple', 'invoice_sent' => 'amber', 'other' => 'gray',
            ];
        @endphp

        <div class="eos-card" style="margin-bottom:14px;">
            <div class="eos-card-header"><div class="eos-card-title">Log Activity</div></div>
            <form method="POST" action="{{ route('activities.store', $client) }}">
                @csrf
                <div class="eos-form-grid">
                    <div class="eos-field">
                        <label class="eos-label">Type *</label>
                        <select name="type" class="eos-select">
                            @foreach ($types as $t)
                                <option value="{{ $t }}" @selected(old('type') === $t)>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="eos-input" placeholder="Short summary…">
                    </div>
                    <div class="eos-field full">
                        <label class="eos-label">Description</label>
                        <textarea name="description" class="eos-textarea" placeholder="Optional details…">{{ old('description') }}</textarea>
                    </div>
                </div>
                <button class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Log Activity</button>
            </form>
        </div>

        <div class="eos-card">
            @forelse ($client->activities as $activity)
                <div class="eos-list-item" style="align-items:flex-start;">
                    <span class="eos-badge badge-act-{{ $typeColor[$activity->type] ?? 'gray' }}" style="margin-top:2px;">{{ strtoupper(str_replace('_', ' ', $activity->type)) }}</span>
                    <div style="flex:1;min-width:0;" x-data="{ open: false }">
                        <div style="font-size:14px;font-weight:600;color:var(--text-primary);">{{ $activity->title }}</div>
                        @if ($activity->description)
                            <div style="font-size:12.5px;color:var(--text-secondary);white-space:pre-wrap;margin-top:3px;"
                                 :class="{ 'eos-clamp': !open }">{{ $activity->description }}</div>
                            @if (strlen($activity->description) > 120)
                                <button type="button" class="eos-card-link" style="background:none;border:none;cursor:pointer;padding:2px 0;"
                                        @click="open = !open" x-text="open ? 'Show less' : 'Show more'"></button>
                            @endif
                        @endif
                        <div style="font-size:11px;color:var(--text-dim);margin-top:4px;" title="{{ $activity->created_at->format('F j, Y g:i A') }}">
                            {{ $activity->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('activities.destroy', $activity) }}" onsubmit="return confirm('Delete this activity?');">
                        @csrf @method('DELETE')
                        <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                    </form>
                </div>
            @empty
                <div class="eos-empty">No activity logged yet.</div>
            @endforelse
        </div>
    </div>

    {{-- WhatsApp Quick Actions --}}
    <div x-show="tab === 'whatsapp'" x-cloak class="eos-card">
        <div class="eos-card-header"><div class="eos-card-title">WhatsApp Quick Actions</div></div>
        @if ($client->whatsapp)
            @php
                $waName = $client->name;
                $msgInvoice = "Hi {$waName}, this is a friendly reminder from Ehlom Digital regarding your pending invoice payment. Kindly clear it at your earliest convenience. Thank you!";
                $msgRenewal = "Hi {$waName}, this is Ehlom Digital. Your hosting/domain is expiring soon. Please reach out to us to renew it and avoid any service interruption. Thank you!";
            @endphp
            <div class="eos-actions" style="flex-wrap:wrap;">
                <a href="{{ \App\Helpers\WhatsAppHelper::link($client->whatsapp) }}" target="_blank" rel="noopener" class="eos-btn eos-btn-secondary">
                    <i class="ti ti-brand-whatsapp"></i> Open Chat
                </a>
                <a href="{{ \App\Helpers\WhatsAppHelper::link($client->whatsapp, $msgInvoice) }}" target="_blank" rel="noopener" class="eos-btn eos-btn-primary">
                    <i class="ti ti-file-invoice"></i> Send Invoice Reminder
                </a>
                <a href="{{ \App\Helpers\WhatsAppHelper::link($client->whatsapp, $msgRenewal) }}" target="_blank" rel="noopener" class="eos-btn eos-btn-primary">
                    <i class="ti ti-refresh"></i> Send Renewal Reminder
                </a>
            </div>
        @else
            <div class="eos-empty">No WhatsApp number saved for this client.</div>
        @endif
    </div>

    {{-- Subscriptions --}}
    <div x-show="tab === 'subscriptions'" x-cloak class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead><tr><th>Product</th><th>Start</th><th>Expiry</th><th>Renewal</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($client->subscriptions as $sub)
                    <tr>
                        <td>{{ $sub->product->name ?? '—' }}</td>
                        <td>{{ $sub->start_date?->format('M j, Y') }}</td>
                        <td>{{ $sub->expiry_date?->format('M j, Y') }}</td>
                        <td>₹{{ number_format($sub->renewal_amount, 0) }}</td>
                        <td><span class="eos-badge badge-{{ $sub->status }}">{{ strtoupper($sub->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="eos-empty">No subscriptions.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Projects --}}
    <div x-show="tab === 'projects'" x-cloak class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead><tr><th>Title</th><th>Start</th><th>Delivery</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($client->projects as $project)
                    <tr>
                        <td>{{ $project->title }}</td>
                        <td>{{ $project->start_date?->format('M j, Y') ?: '—' }}</td>
                        <td>{{ $project->delivery_date?->format('M j, Y') ?: '—' }}</td>
                        <td><span class="eos-badge badge-{{ $project->status }}">{{ strtoupper(str_replace('_', ' ', $project->status)) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="eos-empty">No projects.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Invoices --}}
    <div x-show="tab === 'invoices'" x-cloak class="eos-card" style="padding:0;">
        <table class="eos-table">
            <thead><tr><th>Invoice #</th><th>Total</th><th>Due</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($client->invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>₹{{ number_format($invoice->total, 0) }}</td>
                        <td>{{ $invoice->due_date?->format('M j, Y') ?: '—' }}</td>
                        <td><span class="eos-badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="eos-empty">No invoices.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Domains & Hosting --}}
    <div x-show="tab === 'domains'" x-cloak>
        <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
            <a href="{{ route('domains.create', ['client_id' => $client->id]) }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> Add Domain</a>
        </div>
        <div class="eos-card" style="padding:0;">
            <table class="eos-table">
                <thead><tr><th>Domain</th><th>Registrar</th><th>Hosting Server</th><th>Expiry</th><th>Days Left</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                    @forelse ($client->domains as $domain)
                        @php
                            $days = $domain->days_until_expiry;
                            $dayClass = $days <= 7 ? 'days-red' : ($days <= 30 ? 'days-amber' : 'days-green');
                        @endphp
                        <tr>
                            <td style="font-weight:600;color:var(--text-primary);">{{ $domain->domain_name }}</td>
                            <td>{{ $domain->registrar ?? '—' }}</td>
                            <td>{{ $domain->hosting_server ?? '—' }}</td>
                            <td>{{ $domain->expiry_date?->format('M j, Y') }}</td>
                            <td><span class="eos-row-days {{ $dayClass }}">{{ $days < 0 ? abs($days) . ' overdue' : $days . ' days' }}</span></td>
                            <td><span class="eos-badge badge-{{ $domain->status }}">{{ strtoupper($domain->status) }}</span></td>
                            <td>
                                <div class="eos-actions" style="justify-content:flex-end;">
                                    <a href="{{ route('domains.edit', $domain) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                    <form method="POST" action="{{ route('domains.destroy', $domain) }}" onsubmit="return confirm('Delete this domain?');">
                                        @csrf @method('DELETE')
                                        <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="eos-empty">No domains for this client.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Agreements --}}
    <div x-show="tab === 'agreements'" x-cloak>
        <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
            <a href="{{ route('agreements.create', ['client_id' => $client->id]) }}" class="eos-icon-btn primary"><i class="ti ti-plus"></i> New Agreement</a>
        </div>
        <div class="eos-card" style="padding:0;">
            <table class="eos-table">
                <thead><tr><th>Title</th><th>Type</th><th>Amount</th><th>Start Date</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                    @forelse ($client->agreements as $agreement)
                        <tr>
                            <td style="font-weight:600;color:var(--text-primary);"><a href="{{ route('agreements.show', $agreement) }}">{{ $agreement->title }}</a></td>
                            <td>{{ ucfirst($agreement->type === 'ecommerce' ? 'E-commerce' : $agreement->type) }}</td>
                            <td>₹{{ number_format($agreement->amount, 2) }}</td>
                            <td>{{ $agreement->start_date?->format('M j, Y') }}</td>
                            <td><span class="eos-badge badge-{{ $agreement->status }}">{{ strtoupper($agreement->status) }}</span></td>
                            <td>
                                <div class="eos-actions" style="justify-content:flex-end;">
                                    <a href="{{ route('agreements.pdf', $agreement) }}" class="eos-icon-action edit" title="Download PDF"><i class="ti ti-download"></i></a>
                                    <a href="{{ route('agreements.edit', $agreement) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                                    <form method="POST" action="{{ route('agreements.destroy', $agreement) }}" onsubmit="return confirm('Delete this agreement?');">
                                        @csrf @method('DELETE')
                                        <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="eos-empty">No agreements for this client.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Notes --}}
    <div x-show="tab === 'notes'" x-cloak class="eos-card">
        <div style="font-size:12.5px;color:var(--text-secondary);white-space:pre-wrap;">{{ $client->notes ?: 'No notes.' }}</div>
    </div>
</div>
@endsection
