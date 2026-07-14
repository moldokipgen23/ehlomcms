@extends('layouts.app')

@section('title', isset($template) ? 'Edit Template' : 'Create Template')
@section('subtitle', isset($template) ? 'Update email template' : 'Add a new email template')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;max-width:700px;">
        <div class="eos-card-header">
            <div class="eos-card-title">{{ isset($template) ? 'Edit' : 'New' }} Email Template</div>
        </div>
        <div style="padding:16px;">
            <form method="POST" action="{{ isset($template) ? route('email-templates.update', $template) : route('email-templates.store') }}">
                @csrf
                @if (isset($template)) @method('PUT') @endif

                @if (!isset($template))
                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Key (unique identifier)</label>
                    <input type="text" name="key" value="{{ old('key') }}" required class="eos-input" placeholder="e.g. welcome_tenant">
                    @error('key')<div class="eos-error">{{ $message }}</div>@enderror
                </div>
                @endif

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Name</label>
                    <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" required class="eos-input">
                    @error('name')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}" required class="eos-input">
                    @error('subject')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Body (HTML)</label>
                    <textarea name="body" rows="12" required class="eos-input" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg-card);color:var(--text-primary);font-size:13px;font-family:monospace;resize:vertical;">{{ old('body', $template->body ?? '') }}</textarea>
                    @error('body')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Variables (JSON array of variable names, e.g. ["name","amount"])</label>
                    <input type="text" name="variables" value="{{ old('variables', isset($template) && $template->variables ? json_encode($template->variables) : '') }}" class="eos-input" placeholder='["name","email","amount"]'>
                    @error('variables')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="eos-btn eos-btn-primary">{{ isset($template) ? 'Update' : 'Create' }} Template</button>
                <a href="{{ route('email-templates.index') }}" class="eos-btn" style="border:1px solid var(--border);border-radius:8px;text-decoration:none;color:var(--text-secondary);">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
