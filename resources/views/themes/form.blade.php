@extends('layouts.app')

@section('title', 'New Theme')

@section('subtitle', 'Add a new entry to the template library')

@section('content')
<div style="max-width:680px;" x-data="{ mode: 'base_template' }">
    <form method="POST" action="{{ route('themes.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="mode" x-bind:value="mode">

        <div class="eos-card" style="margin-bottom:14px;">
            <div style="padding:16px;">
                <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                    <label style="flex:1;min-width:160px;padding:12px;border:2px solid var(--border-card);border-radius:9px;cursor:pointer;text-align:center;"
                           x-bind:style="mode === 'base_template' ? 'border-color:var(--accent-blue);' : ''">
                        <input type="radio" x-model="mode" value="base_template" style="display:none;">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);">Use a Built-in Layout</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">Pick a base layout, customize colors</div>
                    </label>
                    <label style="flex:1;min-width:160px;padding:12px;border:2px solid var(--border-card);border-radius:9px;cursor:pointer;text-align:center;"
                           x-bind:style="mode === 'custom_html' ? 'border-color:var(--accent-blue);' : ''">
                        <input type="radio" x-model="mode" value="custom_html" style="display:none;">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);">Paste My Own HTML Design</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">No Blade, no code — just HTML you already know</div>
                    </label>
                    <label style="flex:1;min-width:160px;padding:12px;border:2px solid var(--border-card);border-radius:9px;cursor:pointer;text-align:center;"
                           x-bind:style="mode === 'upload_zip' ? 'border-color:var(--accent-blue);' : ''">
                        <input type="radio" x-model="mode" value="upload_zip" style="display:none;">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);">Upload a theme.zip</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">Same format as the Download button produces</div>
                    </label>
                </div>

                <div class="eos-form-grid">
                    <div class="eos-field full" x-show="mode !== 'upload_zip'">
                        <label class="eos-label">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="eos-input" placeholder="e.g. Festive Restaurant" x-bind:required="mode !== 'upload_zip'">
                        @error('name') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field full" x-show="mode !== 'upload_zip'">
                        <label class="eos-label">Description</label>
                        <textarea name="description" class="eos-input" rows="2">{{ old('description') }}</textarea>
                        @error('description') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eos-field" x-show="mode === 'base_template'">
                        <label class="eos-label">Base Layout *</label>
                        <select name="base_template" class="eos-select">
                            @foreach ($baseTemplates as $key => $label)
                                <option value="{{ $key }}" @selected(old('base_template') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('base_template') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eos-field full" x-show="mode === 'custom_html'">
                        <label class="eos-label">Your HTML Design *</label>
                        <div style="background:var(--bg-hover);border-radius:8px;padding:12px;margin-bottom:8px;font-size:11.5px;color:var(--text-secondary);line-height:1.7;">
                            Paste your full HTML page below. Anywhere you want real client info to appear,
                            type one of these exact tags — they'll be swapped for the real thing automatically:
                            <div style="font-family:monospace;margin-top:6px;color:var(--accent-blue);">
                                {{ implode(' &nbsp; ', $tokenDocs['tenant']) }}
                            </div>
                            To show the product list, wrap ONE product card in these tags — it repeats automatically for every product:
                            <div style="font-family:monospace;margin-top:6px;color:var(--accent-teal);">
                                {{ $tokenDocs['productsOpen'] }} ... your one product card HTML using
                                {{ implode(' ', $tokenDocs['item']) }} ... {{ $tokenDocs['productsClose'] }}
                            </div>
                            <strong>{{ $tokenDocs['buyButton'] }}</strong> automatically becomes a working WhatsApp or
                            payment button depending on what that client has configured — you don't need to build it.
                        </div>
                        <textarea name="custom_html" class="eos-input" rows="14" style="font-family:monospace;font-size:12px;" placeholder="{{ $customHtmlPlaceholder }}">{{ old('custom_html') }}</textarea>
                        @error('custom_html') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eos-field full" x-show="mode === 'upload_zip'">
                        <label class="eos-label">theme.zip *</label>
                        <div style="background:var(--bg-hover);border-radius:8px;padding:12px;margin-bottom:8px;font-size:11.5px;color:var(--text-secondary);line-height:1.7;">
                            Must contain a <code>theme.json</code> at the root (name, description, industries,
                            base_template) and/or a <code>custom.html</code> using the same
                            <code>{{ $tokenDocs['tenant'][0] }}</code>-style tags as the paste-HTML mode. This is exactly
                            the format the <strong>Download</strong> button on any existing theme produces — download
                            one, edit it, re-upload it.
                        </div>
                        <input type="file" name="theme_zip" accept=".zip" class="eos-input">
                        @error('theme_zip') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eos-field" x-show="mode !== 'upload_zip'">
                        <label class="eos-label">Suited For</label>
                        <div style="display:flex;flex-direction:column;gap:6px;margin-top:4px;">
                            @foreach ($businessTypes as $typeKey => $type)
                                <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                                    <input type="checkbox" name="industries[]" value="{{ $typeKey }}" {{ in_array($typeKey, old('industries', [])) ? 'checked' : '' }}> {{ $type['label'] }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="eos-field full">
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                            <input type="checkbox" name="public" value="1" {{ old('public') ? 'checked' : '' }}>
                            Public — visible to every tenant of that business type (leave unchecked for a private/one-off theme you assign yourself)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:8px;">
            <button type="submit" class="eos-btn eos-btn-primary"><i class="ti ti-check"></i> Create Theme</button>
            <a href="{{ route('themes.index') }}" class="eos-btn eos-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
