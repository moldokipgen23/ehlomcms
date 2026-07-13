@extends('layouts.app')

@section('title', 'New Theme')

@section('subtitle', 'Add a new entry to the template library')

@section('content')
<div style="max-width:600px;">
    <form method="POST" action="{{ route('themes.store') }}">
        @csrf

        <div class="eos-card" style="margin-bottom:14px;">
            <div class="eos-card-header">
                <div class="eos-card-title">Theme Details</div>
            </div>
            <div style="padding:16px;">
                <div class="eos-form-grid">
                    <div class="eos-field full">
                        <label class="eos-label">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="eos-input" required placeholder="e.g. Festive Restaurant">
                        @error('name') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field full">
                        <label class="eos-label">Description</label>
                        <textarea name="description" class="eos-input" rows="2">{{ old('description') }}</textarea>
                        @error('description') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Base Layout *</label>
                        <select name="base_template" class="eos-select" required>
                            @foreach ($baseTemplates as $key => $label)
                                <option value="{{ $key }}" @selected(old('base_template') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="eos-page-sub" style="margin-top:4px;">
                            The underlying page structure this theme renders through.
                        </div>
                        @error('base_template') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Suited For</label>
                        <div style="display:flex;flex-direction:column;gap:6px;margin-top:4px;">
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                                <input type="checkbox" name="industries[]" value="shopping" {{ in_array('shopping', old('industries', [])) ? 'checked' : '' }}> Shopping
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                                <input type="checkbox" name="industries[]" value="info" {{ in_array('info', old('industries', [])) ? 'checked' : '' }}> Info / Portfolio
                            </label>
                        </div>
                    </div>
                    <div class="eos-field full">
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                            <input type="checkbox" name="public" value="1" {{ old('public') ? 'checked' : '' }}>
                            Public — visible to clients in their own template picker (leave unchecked for a private/one-off theme you assign yourself)
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
