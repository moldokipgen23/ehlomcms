@extends('tenant.layouts.dashboard')

@section('title', 'Enquiries & Audience')
@section('subtitle', 'Website enquiries, job applications, and newsletter subscribers')

@section('content')
<div style="display:flex;gap:8px;margin-bottom:14px;">
    <a class="eos-btn {{ $tab === 'enquiries' ? 'eos-btn-primary' : 'eos-btn-secondary' }}" href="{{ route('tenant.business-inbox') }}">Enquiries ({{ $enquiries->count() }})</a>
    <a class="eos-btn {{ $tab === 'subscribers' ? 'eos-btn-primary' : 'eos-btn-secondary' }}" href="{{ route('tenant.business-inbox', ['tab' => 'subscribers']) }}">Subscribers ({{ $subscribers->count() }})</a>
</div>
<div class="eos-card"><div class="eos-card-body">
@if($tab === 'subscribers')
    @forelse($subscribers as $subscriber)
        <div class="eos-row"><div class="eos-init"><i class="ti ti-mail"></i></div><div class="eos-row-main"><div class="eos-row-name">{{ $subscriber->email }}</div><div class="eos-row-type">{{ $subscriber->name ?: 'Subscriber' }} · {{ ucfirst($subscriber->status) }} · {{ $subscriber->created_at->format('d M Y') }}</div></div></div>
    @empty <div class="eos-empty">No newsletter subscribers yet.</div> @endforelse
@else
    @forelse($enquiries as $enquiry)
        <div class="eos-row" style="align-items:flex-start;">
            <div class="eos-init"><i class="ti {{ $enquiry->type === 'career_application' ? 'ti-user-check' : 'ti-message' }}"></i></div>
            <div class="eos-row-main"><div class="eos-row-name">{{ $enquiry->subject ?: 'Website enquiry' }}</div><div class="eos-row-type">{{ $enquiry->name }} · {{ $enquiry->email }} @if($enquiry->phone) · {{ $enquiry->phone }} @endif · {{ $enquiry->created_at->format('d M Y, h:i A') }}</div><div style="margin-top:8px;color:var(--text-secondary);font-size:12px;line-height:1.6;">{{ $enquiry->message }}</div>@if($enquiry->meta['resume'] ?? null)<a href="{{ Storage::url($enquiry->meta['resume']) }}" target="_blank" class="eos-btn eos-btn-secondary" style="margin-top:8px;">View resume</a>@endif</div>
            <form method="POST" action="{{ route('tenant.business-inbox.update', $enquiry->id) }}" style="padding:0!important;display:flex;gap:6px;">@csrf @method('PATCH')<select class="eos-input" name="status" onchange="this.form.submit()"><option value="new" @selected($enquiry->status==='new')>New</option><option value="contacted" @selected($enquiry->status==='contacted')>Contacted</option><option value="closed" @selected($enquiry->status==='closed')>Closed</option></select></form>
            <form method="POST" action="{{ route('tenant.business-inbox.destroy', $enquiry->id) }}" style="padding:0!important;">@csrf @method('DELETE')<button class="eos-icon-btn danger" title="Delete"><i class="ti ti-trash"></i></button></form>
        </div>
    @empty <div class="eos-empty">No enquiries yet. Public contact and career forms will appear here.</div> @endforelse
@endif
</div></div>
@endsection
