@extends('layouts.app')

@section('title', 'Add-on Products')

@section('subtitle', 'What you sell to tenants, and at what price')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div class="eos-page-title" style="font-size:16px;font-weight:700;color:var(--text-primary);">
        {{ $addons->count() }} Add-on Product{{ $addons->count() !== 1 ? 's' : '' }}
    </div>
    <a href="{{ route('addon-products.create') }}" class="eos-btn eos-btn-primary">
        <i class="ti ti-plus"></i> New Add-on
    </a>
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Visibility</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($addons as $addon)
            <tr>
                <td style="font-weight:600;">
                    <i class="ti {{ $addon->icon }}" style="margin-right:6px;color:var(--text-muted);"></i>
                    {{ $addon->name }}
                    @if ($addon->description)
                        <div style="font-size:11px;color:var(--text-dim);font-weight:400;margin-top:2px;">{{ $addon->description }}</div>
                    @endif
                </td>
                <td>₹{{ number_format($addon->price, 0) }}/mo</td>
                <td>
                    <form action="{{ route('addon-products.toggle-active', $addon) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="eos-badge {{ $addon->active ? 'badge-active' : 'badge-pending' }}" style="border:none;cursor:pointer;">
                            {{ $addon->active ? 'Visible to clients' : 'Hidden' }}
                        </button>
                    </form>
                </td>
                <td style="display:flex;gap:6px;">
                    <a href="{{ route('addon-products.edit', $addon) }}" class="eos-btn eos-btn-secondary" style="font-size:10px;padding:4px 10px;">
                        <i class="ti ti-edit"></i> Edit
                    </a>
                    <form action="{{ route('addon-products.destroy', $addon) }}" method="POST" onsubmit="return confirm('Delete this add-on product?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="eos-btn eos-btn-danger" style="font-size:10px;padding:4px 10px;">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4"><div class="eos-empty">No add-on products yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
