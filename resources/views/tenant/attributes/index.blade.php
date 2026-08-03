@extends('tenant.layouts.dashboard')

@section('title', 'Product Attributes')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header"><div class="eos-card-title">Attributes</div></div>
        <form method="POST" action="{{ route('tenant.attributes.store') }}" style="padding:16px;border-bottom:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;">
            @csrf
            <input class="eos-input" name="name" placeholder="Attribute name, e.g. Material" required style="flex:1;min-width:220px;">
            <input class="eos-input" type="number" name="sort_order" placeholder="Sort" style="width:90px;">
            <button class="eos-btn eos-btn-primary" style="border:none;"><i class="ti ti-plus"></i> Add</button>
        </form>
        @forelse($attributes as $attribute)
            <div style="padding:16px;border-bottom:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:8px;justify-content:space-between;">
                    <div><div class="eos-row-name">{{ $attribute->name }}</div><div class="eos-row-type">{{ $attribute->values->count() }} values</div></div>
                    <form method="POST" action="{{ route('tenant.attributes.destroy', $attribute->id) }}" onsubmit="return confirm('Delete this attribute?');">@csrf @method('DELETE')<button class="eos-logout"><i class="ti ti-trash"></i></button></form>
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;">
                    @foreach($attribute->values as $value)
                        <span class="eos-badge badge-info">{{ $value->name }}</span>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('tenant.attributes.values.store', $attribute->id) }}" style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;">
                    @csrf
                    <input class="eos-input" name="name" placeholder="Value name" required style="flex:1;min-width:180px;">
                    <input class="eos-input" name="hex_code" placeholder="#HEX optional" style="width:130px;">
                    <button class="eos-btn eos-btn-secondary" style="border:1px solid var(--border);"><i class="ti ti-plus"></i> Add Value</button>
                </form>
            </div>
        @empty
            <div class="eos-empty" style="padding:32px 16px;">No attributes yet.</div>
        @endforelse
    </div>
</div>
@endsection
