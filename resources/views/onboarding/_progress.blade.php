@php
    $steps = ['info', 'theme', 'modules', 'domain', 'done'];
    $labels = ['Business Info', 'Theme', 'Modules', 'Domain', 'Done'];
@endphp
<div style="display:flex;gap:4px;margin-bottom:20px;">
    @foreach ($steps as $i => $s)
        @php $num = $i + 1; @endphp
        <div style="flex:1;text-align:center;padding:8px 4px;border-radius:6px;font-size:11px;font-weight:600;{{ $num <= $current ? 'background:var(--accent-teal);color:white;' : 'background:var(--bg-hover);color:var(--text-dim);' }}">
            {{ $num }}. {{ $labels[$i] }}
        </div>
    @endforeach
</div>
