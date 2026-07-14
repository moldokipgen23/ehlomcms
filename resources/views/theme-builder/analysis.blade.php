@extends('layouts.app')

@section('title', 'Theme Builder: Analysis')
@section('subtitle', 'Design analysis results')

@section('content')
<div style="max-width:800px;">
    <div class="eos-card" style="margin-bottom:16px;">
        <div class="eos-card-header">
            <div class="eos-card-title"><i class="ti ti-analyze"></i> Design Analysis</div>
        </div>
        <div class="eos-card-body" style="padding:20px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div style="background:var(--bg-hover);border-radius:8px;padding:12px;">
                    <div style="font-size:10px;color:var(--accent-teal);font-weight:600;text-transform:uppercase;">Sections Found</div>
                    <div style="font-size:20px;font-weight:700;">{{ count($analysis['sections'] ?? []) }}</div>
                    <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">{{ implode(', ', $analysis['sections'] ?? []) }}</div>
                </div>
                <div style="background:var(--bg-hover);border-radius:8px;padding:12px;">
                    <div style="font-size:10px;color:var(--accent-teal);font-weight:600;text-transform:uppercase;">Components</div>
                    <div style="font-size:20px;font-weight:700;">{{ count($analysis['components'] ?? []) }}</div>
                    <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">{{ implode(', ', $analysis['components'] ?? []) }}</div>
                </div>
            </div>

            @if (!empty($analysis['colors']))
                <div style="margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:600;margin-bottom:6px;">Colors</div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        @foreach (array_slice($analysis['colors'], 0, 8) as $color)
                            <div style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:{{ $color }};" title="{{ $color }}"></div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($analysis['fonts']))
                <div style="margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:600;margin-bottom:6px;">Fonts</div>
                    <div style="font-size:12px;color:var(--text-secondary);">{{ implode(', ', $analysis['fonts']) }}</div>
                </div>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('theme-builder.generate') }}">
        @csrf
        <input type="hidden" name="analysis" value="{{ json_encode($analysis) }}">
        <div class="eos-card" style="margin-bottom:16px;">
            <div class="eos-card-header">
                <div class="eos-card-title"><i class="ti ti-settings"></i> Generate Theme</div>
            </div>
            <div class="eos-card-body" style="padding:20px;">
                <div class="eos-field">
                    <label class="eos-label">Theme Name <span class="text-red-500">*</span></label>
                    <input type="text" name="theme_name" class="eos-input" required placeholder="e.g. Restaurant Pro">
                </div>
                <div class="eos-field">
                    <label class="eos-label">Business Type</label>
                    <input type="text" class="eos-input" value="{{ $analysis['business_type'] }}" readonly>
                    <input type="hidden" name="business_type" value="{{ $analysis['business_type'] }}">
                </div>

                <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:8px;">
                    <div style="font-size:12px;font-weight:600;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                        <i class="ti ti-robot" style="color:var(--accent-teal);"></i> AI Generation (optional)
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">AI Provider</label>
                        <select name="ai_provider" class="eos-input" id="aiProviderSelect">
                            <option value="openai">OpenAI (GPT-4o)</option>
                            <option value="anthropic">Anthropic (Claude)</option>
                        </select>
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">API Key</label>
                        <input type="password" name="ai_api_key" class="eos-input" placeholder="sk-... or sk-ant-..." id="aiApiKeyInput">
                        <div style="font-size:10px;color:var(--text-dim);margin-top:4px;">Leave empty to use static template generation (no AI)</div>
                    </div>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <a href="{{ route('theme-builder.index') }}" class="eos-btn eos-btn-secondary" style="padding:10px 20px;">Back</a>
            <button type="submit" class="eos-btn eos-btn-primary" style="padding:10px 24px;"><i class="ti ti-wand"></i> Generate Theme</button>
        </div>
    </form>
</div>
@endsection
