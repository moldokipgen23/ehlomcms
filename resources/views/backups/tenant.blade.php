@extends('layouts.app')

@section('title', 'Tenant Backups')
@section('subtitle', "Backup history for {$tenant->name}")

@section('content')
<div style="display:flex;gap:8px;margin-bottom:16px;">
    <form method="POST" action="{{ route('backups.tenant-assets-backup', $tenant) }}" style="display:inline;">
        @csrf
        <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-cloud-up"></i> Backup Assets Now</button>
    </form>
    <a href="{{ route('backups.index') }}" class="eos-btn" style="font-size:12px;">&larr; Back</a>
</div>

<table class="eos-table">
    <thead>
        <tr>
            <th>Type</th>
            <th>File</th>
            <th>Size</th>
            <th>Date</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @php $backups = $tenant->backups()->orderByDesc('created_at')->get(); @endphp
        @forelse ($backups as $tb)
            <tr>
                <td><span class="eos-badge badge-{{ $tb->type === 'database' ? 'active' : 'draft' }}">{{ $tb->type }}</span></td>
                <td style="font-size:12px;">{{ $tb->filename }}</td>
                <td style="font-size:12px;">{{ number_format($tb->size / 1024, 1) }} KB</td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $tb->created_at->format('M j, Y H:i') }}</td>
                <td><span class="eos-badge badge-active">{{ $tb->status }}</span></td>
                <td style="text-align:right;">
                    @if ($tb->type === 'assets')
                        <a href="{{ route('backups.tenant-download', $tb) }}" class="eos-btn" style="font-size:10px;padding:3px 8px;border:1px solid var(--border);border-radius:6px;text-decoration:none;color:var(--text-secondary);"><i class="ti ti-download"></i></a>
                    @endif
                    <form method="POST" action="{{ route('backups.tenant-restore-assets', [$tenant, $tb]) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="eos-btn" style="font-size:10px;padding:3px 8px;border:1px solid #f59e0b;border-radius:6px;color:#f59e0b;background:none;cursor:pointer;" onclick="return confirm('Restore these assets? This will overwrite current files.');"><i class="ti ti-refresh"></i></button>
                    </form>
                    <form method="POST" action="{{ route('backups.tenant-destroy', $tb) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="eos-btn" style="font-size:10px;padding:3px 8px;border:1px solid #ef4444;border-radius:6px;color:#ef4444;background:none;cursor:pointer;" onclick="return confirm('Delete this backup?');"><i class="ti ti-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="eos-empty">No backups for this tenant yet.</div></td></tr>
        @endforelse
    </tbody>
</table>
@endsection
