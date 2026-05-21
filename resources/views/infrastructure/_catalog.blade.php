<div class="eos-card" style="padding:0;">
    <table class="eos-table">
        <thead>
            <tr><th>Name</th><th>Price</th><th>Billing</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td style="font-weight:600;color:var(--text-primary);">{{ $item->name }}</td>
                    <td>₹{{ number_format($item->price, 2) }}</td>
                    <td>{{ \App\Models\Product::BILLING_LABELS[$item->billing_cycle] ?? ucfirst($item->billing_cycle) }}</td>
                    <td><span class="eos-badge badge-{{ $item->status }}">{{ strtoupper($item->status) }}</span></td>
                    <td>
                        <div class="eos-actions" style="justify-content:flex-end;">
                            <a href="{{ route('products.edit', $item) }}" class="eos-icon-action edit" title="Edit"><i class="ti ti-pencil"></i></a>
                            <form method="POST" action="{{ route('products.destroy', $item) }}" onsubmit="return confirm('Delete this item?');">
                                @csrf @method('DELETE')
                                <button class="eos-icon-action del" title="Delete"><i class="ti ti-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="eos-empty">{{ $empty }}</div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
