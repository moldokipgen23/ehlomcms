@extends('layouts.app')

@section('title', $project->title)
@section('subtitle', 'Project detail')

@section('topbar-action')
    <a href="{{ route('projects.edit', $project) }}" class="eos-icon-btn"><i class="ti ti-pencil"></i> Edit</a>
@endsection

@section('content')
    @php
        $productsTotal = $project->products_total;
        $gst = round($productsTotal * 0.18, 2);
    @endphp

    <div class="eos-card" style="margin-bottom:14px;">
        <div class="eos-card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="eos-card-title">{{ $project->title }}</div>
            <span class="eos-badge badge-{{ $project->status }}">{{ strtoupper(str_replace('_', ' ', $project->status)) }}</span>
        </div>
        <div class="eos-form-grid">
            <div>
                <div class="eos-label">Client</div>
                <div style="font-size:12.5px;">
                    <a href="{{ route('clients.show', $project->client) }}">{{ $project->client->name ?? '—' }}</a>
                </div>
            </div>
            <div>
                <div class="eos-label">Start Date</div>
                <div style="font-size:12.5px;color:var(--text-secondary);">{{ $project->start_date?->format('M j, Y') ?: '—' }}</div>
            </div>
            <div>
                <div class="eos-label">Delivery Date</div>
                <div style="font-size:12.5px;color:var(--text-secondary);">{{ $project->delivery_date?->format('M j, Y') ?: '—' }}</div>
            </div>
        </div>
        @if ($project->description)
            <div style="margin-top:10px;">
                <div class="eos-label">Description</div>
                <div style="font-size:12.5px;color:var(--text-secondary);white-space:pre-wrap;">{{ $project->description }}</div>
            </div>
        @endif
        @if ($project->notes)
            <div style="margin-top:10px;">
                <div class="eos-label">Internal Notes</div>
                <div style="font-size:12.5px;color:var(--text-secondary);white-space:pre-wrap;">{{ $project->notes }}</div>
            </div>
        @endif
    </div>

    {{-- Included products --}}
    <div class="eos-card" style="margin-bottom:14px;padding:0;">
        <div class="eos-card-header" style="padding:14px 14px 0;"><div class="eos-card-title">Included Products &amp; Services</div></div>
        <table class="eos-table">
            <thead>
                <tr><th>Product / Service</th><th>Qty</th><th>Unit Price</th><th style="text-align:right;">Line Total</th></tr>
            </thead>
            <tbody>
                @forelse ($project->products as $product)
                    <tr>
                        <td style="font-weight:600;color:var(--text-primary);">{{ $product->name }}</td>
                        <td>{{ rtrim(rtrim(number_format($product->pivot->quantity, 2), '0'), '.') }}</td>
                        <td>₹{{ number_format($product->pivot->unit_price, 2) }}</td>
                        <td style="text-align:right;">₹{{ number_format($product->pivot->quantity * $product->pivot->unit_price, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="eos-empty">No products added to this project yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($project->products->isNotEmpty())
            <div style="padding:12px 14px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;">
                <div style="font-weight:700;font-size:14px;">Project Total: ₹{{ number_format($productsTotal, 2) }}</div>
            </div>
        @endif
    </div>

    {{-- Invoice --}}
    <div class="eos-card" style="margin-bottom:14px;">
        <div class="eos-card-header"><div class="eos-card-title">Invoice</div></div>
        @if ($project->invoice)
            <p style="font-size:12px;color:var(--text-secondary);margin-bottom:10px;">
                Invoice <strong>{{ $project->invoice->invoice_number }}</strong> —
                <span class="eos-badge badge-{{ $project->invoice->status }}">{{ strtoupper($project->invoice->status) }}</span>
                · Total ₹{{ number_format($project->invoice->total, 2) }}
            </p>
            <a href="{{ route('invoices.show', $project->invoice) }}" class="eos-btn eos-btn-primary">
                <i class="ti ti-file-invoice"></i> View Invoice
            </a>
        @else
            <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:10px;">
                Creates a draft invoice with every product above as a line item (GST 18% applied). You can review and edit it before sending.
            </p>
            <form method="POST" action="{{ route('projects.generateInvoice', $project) }}">
                @csrf
                <button class="eos-btn eos-btn-primary" {{ $project->products->isEmpty() ? 'disabled' : '' }}>
                    <i class="ti ti-file-plus"></i> Generate Invoice
                </button>
            </form>
        @endif
    </div>

    {{-- Active subscriptions tied to this project --}}
    <div class="eos-card" style="margin-bottom:14px;padding:0;">
        <div class="eos-card-header" style="padding:14px 14px 0;display:flex;justify-content:space-between;align-items:center;">
            <div class="eos-card-title">Ongoing Services &amp; Subscriptions</div>
            <a href="{{ route('subscriptions.create', ['project_id' => $project->id, 'client_id' => $project->client_id]) }}" class="eos-icon-btn">
                <i class="ti ti-plus"></i> Add
            </a>
        </div>
        @if ($project->subscriptions->isNotEmpty())
            <table class="eos-table">
                <thead>
                    <tr><th>Service</th><th>Renews</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($project->subscriptions as $sub)
                        <tr>
                            <td style="font-weight:600;">{{ $sub->product->name ?? '—' }}</td>
                            <td>{{ $sub->expiry_date?->format('M j, Y') ?: '—' }}</td>
                            <td>₹{{ number_format((float) $sub->renewal_amount, 2) }}</td>
                            <td><span class="eos-badge badge-{{ $sub->status }}">{{ strtoupper($sub->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding:14px;"><div class="eos-empty">No subscriptions tied to this project yet.</div></div>
        @endif
    </div>

    {{-- Project Completion --}}
    <div class="eos-card">
        <div class="eos-card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="eos-card-title">Project Completion</div>
            @if ($project->completed_at)
                <span style="font-size:11px;color:var(--accent-teal, #10b981);">
                    <i class="ti ti-check"></i> Completed {{ $project->completed_at->format('M j, Y') }}
                </span>
            @endif
        </div>
        <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:14px;line-height:1.6;">
            Write up what was delivered and any upsell ideas. Upload a final deliverable PDF if you have one.
            The summary PDF is generated automatically — no client signature required.
        </p>

        <form method="POST" action="{{ route('projects.saveCompletion', $project) }}" enctype="multipart/form-data">
            @csrf
            <div class="eos-field">
                <label class="eos-label">Completion Summary</label>
                <textarea name="completion_summary" rows="5" class="eos-input" placeholder="What was delivered, scope notes, anything the client should know on handover...">{{ old('completion_summary', $project->completion_summary) }}</textarea>
                @error('completion_summary') <div class="eos-error">{{ $message }}</div> @enderror
            </div>

            <div class="eos-field">
                <label class="eos-label">Future Plans / Upsell Notes</label>
                <textarea name="upsell_notes" rows="4" class="eos-input" placeholder="Suggested next phase, recommended add-ons, AMC pitch...">{{ old('upsell_notes', $project->upsell_notes) }}</textarea>
                @error('upsell_notes') <div class="eos-error">{{ $message }}</div> @enderror
            </div>

            <div class="eos-field">
                <label class="eos-label">Final Deliverable PDF (optional)</label>
                @if ($project->deliverable_pdf)
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;font-size:12px;">
                        <a href="{{ Storage::url($project->deliverable_pdf) }}" target="_blank" class="eos-btn eos-btn-secondary">
                            <i class="ti ti-file-text"></i> View current PDF
                        </a>
                        <label style="display:flex;align-items:center;gap:6px;color:var(--text-secondary);">
                            <input type="checkbox" name="remove_deliverable_pdf" value="1"> Remove
                        </label>
                    </div>
                @endif
                <input type="file" name="deliverable_pdf" accept="application/pdf" class="eos-input">
                <div style="font-size:10.5px;color:var(--text-dim);margin-top:4px;">PDF only, max 10 MB.</div>
                @error('deliverable_pdf') <div class="eos-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-top:10px;">
                <button type="submit" class="eos-btn eos-btn-secondary">
                    <i class="ti ti-device-floppy"></i> Save Draft
                </button>
                @if ($project->status !== 'completed')
                    <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-secondary);">
                        <input type="checkbox" name="mark_complete" value="1"> Mark project as completed
                    </label>
                @endif
            </div>
        </form>

        <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('projects.summaryPdf', $project) }}" class="eos-btn eos-btn-primary" target="_blank">
                <i class="ti ti-file-download"></i> Download Summary PDF
            </a>
            <form method="POST" action="{{ route('projects.sendCompletionEmail', $project) }}"
                  onsubmit="return confirm('Send the completion email + summary PDF to {{ $project->client->email ?? 'the client' }}?');">
                @csrf
                <button class="eos-btn eos-btn-primary" {{ $project->client?->email ? '' : 'disabled' }}>
                    <i class="ti ti-mail"></i> Send Completion Email
                </button>
            </form>
        </div>
        @unless ($project->client?->email)
            <div style="margin-top:8px;font-size:11px;color:var(--accent-red, #ef4444);">
                Client has no email address — add one on the client page to enable sending.
            </div>
        @endunless
    </div>
@endsection
