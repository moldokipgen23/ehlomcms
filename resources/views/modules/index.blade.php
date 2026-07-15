@extends('layouts.app')

@section('title', 'Feature Bundles')
@section('subtitle', 'What each business type includes — Free, Pro, and Premium tiers')

@section('content')

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
    @foreach ($bundles as $typeKey => $bundle)
        @php
            $count = $tenantCounts[$typeKey] ?? 0;
            $freeCount = count($bundle['free'] ?? []);
            $proCount = count($bundle['pro'] ?? []);
            $premiumCount = count($bundle['premium'] ?? []);
        @endphp
        <a href="{{ route('modules.show', $typeKey) }}" style="text-decoration:none;">
            <div class="eos-card" style="padding:0;overflow:hidden;cursor:pointer;transition:all .2s;border:2px solid transparent;" onmouseover="this.style.borderColor='var(--accent-teal)'" onmouseout="this.style.borderColor='transparent'">
                {{-- Header --}}
                <div style="background:linear-gradient(135deg,#0f172a,#1e293b);padding:20px;color:white;">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="ti {{ $bundle['icon'] }}" style="font-size:22px;color:var(--accent-teal);"></i>
                        </div>
                        <div>
                            <div style="font-size:17px;font-weight:700;">{{ $bundle['label'] }}</div>
                            <div style="font-size:11px;opacity:0.7;">{{ $count }} active tenant{{ $count !== 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                    <div style="font-size:12px;opacity:0.8;line-height:1.5;">{{ $bundle['description'] }}</div>
                </div>

                {{-- Tier Summary --}}
                <div style="padding:16px 20px;">
                    <div style="display:flex;gap:16px;">
                        {{-- Free --}}
                        <div style="flex:1;text-align:center;padding:12px 8px;background:#f0fdf4;border-radius:8px;">
                            <div style="font-size:22px;font-weight:800;color:#16a34a;">{{ $freeCount }}</div>
                            <div style="font-size:10px;color:#16a34a;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Free</div>
                            <div style="font-size:9px;color:#94a3b8;margin-top:2px;">Included</div>
                        </div>
                        {{-- Pro --}}
                        <div style="flex:1;text-align:center;padding:12px 8px;background:#fffbeb;border-radius:8px;">
                            <div style="font-size:22px;font-weight:800;color:#d97706;">{{ $proCount }}</div>
                            <div style="font-size:10px;color:#d97706;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Pro</div>
                            <div style="font-size:9px;color:#94a3b8;margin-top:2px;">Add-on</div>
                        </div>
                        {{-- Premium --}}
                        <div style="flex:1;text-align:center;padding:12px 8px;background:#faf5ff;border-radius:8px;">
                            <div style="font-size:22px;font-weight:800;color:#9333ea;">{{ $premiumCount }}</div>
                            <div style="font-size:10px;color:#9333ea;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Premium</div>
                            <div style="font-size:9px;color:#94a3b8;margin-top:2px;">Add-on</div>
                        </div>
                    </div>

                    {{-- Preview features --}}
                    <div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:4px;">
                        @foreach (array_slice($bundle['free'] ?? [], 0, 5) as $feat)
                            <span style="font-size:10px;padding:3px 8px;background:#f1f5f9;border-radius:4px;color:#475569;">{{ $feat['name'] }}</span>
                        @endforeach
                        @if ($freeCount > 5)
                            <span style="font-size:10px;padding:3px 8px;background:#f1f5f9;border-radius:4px;color:#94a3b8;">+{{ $freeCount - 5 }} more</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    @endforeach
</div>

@endsection
