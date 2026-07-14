@extends('tenant.layouts.dashboard')

@section('title', 'Services')

@section('topbar-action')
    <a href="{{ route('tenant.services.create') }}" class="eos-btn eos-btn-primary" style="text-decoration:none;">
        <i class="ti ti-plus"></i> Add Service
    </a>
@endsection

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">Your Services</div>
            <span class="eos-card-link">{{ $services->count() }} items</span>
        </div>

        @forelse ($services as $service)
            <div class="eos-list-item">
                <div style="width:60px;height:60px;border-radius:8px;overflow:hidden;flex-shrink:0;background:var(--bg-hover);">
                    @if ($service->photo)
                        <img src="{{ Storage::url($service->photo) }}" alt="{{ $service->name }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-dim);"><i class="ti ti-briefcase-2"></i></div>
                    @endif
                </div>
                <div style="flex:1;min-width:0;padding:0 12px;">
                    <div class="eos-row-name">{{ $service->name }}</div>
                    <div class="eos-row-type">{{ $service->description ? Str::limit($service->description, 70) : '' }}</div>
                </div>
                <div style="text-align:right;">
                    <div class="eos-amt">{{ $service->price !== null ? '₹' . number_format($service->price, 2) : '—' }}</div>
                    <div style="display:flex;gap:6px;margin-top:4px;justify-content:flex-end;">
                        <a href="{{ route('tenant.services.edit', $service->id) }}" class="eos-logout" title="Edit"><i class="ti ti-pencil"></i></a>
                        <form method="POST" action="{{ route('tenant.services.destroy', $service->id) }}" onsubmit="return confirm('Delete this service?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="eos-logout" title="Delete"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:32px 16px;">
                No services yet. <a href="{{ route('tenant.services.create') }}" style="color:var(--accent-blue);">Add your first service</a>.
            </div>
        @endforelse
    </div>
</div>
@endsection
