@extends('layouts.app')

@section('title', 'Backups')
@section('subtitle', 'Database and asset backup management')

@section('content')
<div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
    <form method="POST" action="{{ route('backups.run') }}" style="display:inline;">
        @csrf
        <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-database"></i> Run Database Backup</button>
    </form>
</div>

<div class="eos-row" style="display:flex;gap:16px;flex-wrap:wrap;">
    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Database Backups</div>
            <span class="eos-card-link">{{ count($backups) }} file{{ count($backups) !== 1 ? 's' : '' }}</span>
        </div>
        @forelse ($backups as $backup)
            <div class="eos-list-item">
                <div class="eos-init" style="background:var(--bg-hover);"><i class="ti ti-file-zip"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name" style="font-size:12px;">{{ $backup['filename'] }}</div>
                    <div class="eos-row-type">{{ number_format($backup['size'] / 1024, 1) }} KB &middot; {{ \Carbon\Carbon::createFromTimestamp($backup['date'])->diffForHumans() }}</div>
                </div>
                <div style="display:flex;gap:6px;">
                    <a href="{{ route('backups.download', basename($backup['filename'])) }}" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;text-decoration:none;color:var(--text-secondary);"><i class="ti ti-download"></i></a>
                    <form method="POST" action="{{ route('backups.restore') }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="backup_file" value="{{ $backup['filename'] }}">
                        <button type="submit" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid #f59e0b;border-radius:6px;color:#f59e0b;background:none;cursor:pointer;" onclick="return confirm('Restore this backup? This will overwrite the current database.');"><i class="ti ti-refresh"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:24px 16px;">No backups yet. Run your first backup above.</div>
        @endforelse
    </div>

    <div class="eos-card" style="flex:1;min-width:300px;">
        <div class="eos-card-header">
            <div class="eos-card-title">Tenant Assets</div>
            <span class="eos-card-link">{{ count($assetDirs) }} tenant{{ count($assetDirs) !== 1 ? 's' : '' }}</span>
        </div>
        @forelse ($assetDirs as $dir)
            <div class="eos-list-item">
                <div class="eos-init" style="background:var(--bg-hover);"><i class="ti ti-folder"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name" style="font-size:12px;">{{ $dir['tenant_name'] }}</div>
                    <div class="eos-row-type">{{ number_format($dir['files']) }} files &middot; {{ number_format($dir['size'] / 1024, 1) }} KB</div>
                </div>
                <div style="display:flex;gap:4px;">
                    <form method="POST" action="{{ route('backups.tenant-assets-backup', $dir['tenant_id']) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="eos-btn" style="font-size:10px;padding:3px 8px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;"><i class="ti ti-cloud-up"></i> Backup</button>
                    </form>
                    <a href="{{ route('backups.tenant-assets', $dir['tenant_id']) }}" class="eos-btn" style="font-size:10px;padding:3px 8px;border:1px solid var(--border);border-radius:6px;text-decoration:none;color:var(--text-secondary);"><i class="ti ti-history"></i></a>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:24px 16px;">No tenant assets found.</div>
        @endforelse
    </div>
</div>

@if (isset($tenantBackups) && count($tenantBackups))
<div class="eos-card" style="margin-top:16px;">
    <div class="eos-card-header">
        <div class="eos-card-title">Per-Tenant Backup History</div>
        <span class="eos-card-link">{{ $tenantBackups->total() }} total</span>
    </div>
    <table class="eos-table">
        <thead>
            <tr>
                <th>Tenant</th>
                <th>Type</th>
                <th>File</th>
                <th>Size</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tenantBackups as $tb)
                <tr>
                    <td style="font-weight:600;font-size:12px;">{{ $tb->tenant?->name ?? 'Deleted' }}</td>
                    <td><span class="eos-badge badge-{{ $tb->type === 'database' ? 'active' : 'draft' }}">{{ $tb->type }}</span></td>
                    <td style="font-size:11px;">{{ $tb->filename }}</td>
                    <td style="font-size:11px;">{{ number_format($tb->size / 1024, 1) }} KB</td>
                    <td style="font-size:11px;color:var(--text-muted);">{{ $tb->created_at->diffForHumans() }}</td>
                    <td style="text-align:right;">
                        @if ($tb->type === 'assets')
                            <a href="{{ route('backups.tenant-download', $tb) }}" class="eos-btn" style="font-size:10px;padding:3px 8px;border:1px solid var(--border);border-radius:6px;text-decoration:none;color:var(--text-secondary);"><i class="ti ti-download"></i></a>
                        @endif
                        <form method="POST" action="{{ route('backups.tenant-destroy', $tb) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="eos-btn" style="font-size:10px;padding:3px 8px;border:1px solid #ef4444;border-radius:6px;color:#ef4444;background:none;cursor:pointer;" onclick="return confirm('Delete this backup?');"><i class="ti ti-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding:12px 16px;">
        {{ $tenantBackups->links() }}
    </div>
</div>
@endif
@endsection
