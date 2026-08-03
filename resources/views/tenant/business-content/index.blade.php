@extends('tenant.layouts.dashboard')

@section('title', $definition['plural'])
@section('subtitle', 'Manage ' . strtolower($definition['plural']) . ' shown on your website')

@section('topbar-action')
    <a href="{{ route('tenant.business-content.create', ['type' => $type]) }}" class="eos-btn eos-btn-primary"><i class="ti ti-plus"></i> Add {{ $definition['singular'] }}</a>
@endsection

@section('content')
<div class="eos-card">
    <div class="eos-card-header"><div><div class="eos-card-title">{{ $definition['plural'] }}</div><div class="eos-row-type">{{ $items->count() }} records</div></div></div>
    <div class="eos-card-body">
        @forelse ($items as $item)
            <div class="eos-row" style="align-items:center;">
                <div class="eos-init" style="overflow:hidden;">
                    @if($item->image)<img src="{{ Storage::url($item->image) }}" alt="" style="width:100%;height:100%;object-fit:cover;">@else<i class="ti ti-briefcase"></i>@endif
                </div>
                <div class="eos-row-main"><div class="eos-row-name">{{ $item->title }}</div><div class="eos-row-type">{{ $item->subtitle ?: 'No subtitle' }} · {{ $item->is_active ? 'Visible' : 'Hidden' }}</div></div>
                <a href="{{ route('tenant.business-content.edit', ['type' => $type, 'id' => $item->id]) }}" class="eos-icon-btn" title="Edit"><i class="ti ti-pencil"></i></a>
                <form method="POST" action="{{ route('tenant.business-content.destroy', ['type' => $type, 'id' => $item->id]) }}" onsubmit="return confirm('Delete this item?')" style="padding:0!important;">
                    @csrf @method('DELETE')<button class="eos-icon-btn danger" title="Delete"><i class="ti ti-trash"></i></button>
                </form>
            </div>
        @empty
            <div class="eos-empty"><i class="ti ti-layout-grid-add"></i><strong>No {{ strtolower($definition['plural']) }} yet</strong><span>Add the first record to publish this section on your website.</span></div>
        @endforelse
    </div>
</div>
@endsection
