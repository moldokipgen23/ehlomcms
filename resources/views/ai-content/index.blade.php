@extends('layouts.app')

@section('title', 'AI Content')
@section('subtitle', 'Generate content for tenant websites using AI')

@section('content')
<div style="font-size:11.5px;color:var(--text-secondary);background:var(--bg-hover);border-radius:8px;padding:12px;margin-bottom:16px;line-height:1.6;">
    Select a tenant with AI content enabled, choose content type, and describe what you need. AI-generated content will appear below for copy-paste.
</div>

<form id="aiContentForm" style="margin-bottom:16px;">
    @csrf
    <div class="eos-row" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <label class="eos-label">Tenant</label>
            <select id="tenantSelect" class="eos-input" required>
                <option value="">Select tenant…</option>
                @foreach ($tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1;min-width:150px;">
            <label class="eos-label">Content Type</label>
            <select id="typeSelect" class="eos-input" required>
                <option value="about_us">About Us</option>
                <option value="product_description">Product Description</option>
                <option value="blog_post">Blog Post</option>
                <option value="service_description">Service Description</option>
            </select>
        </div>
    </div>
    <div class="eos-field" style="margin-top:12px;">
        <label class="eos-label">Prompt — describe what you want</label>
        <textarea id="promptInput" class="eos-input" rows="3" placeholder="e.g. Write about our premium organic skincare line for eco-conscious millennials…" required></textarea>
    </div>
    <button type="submit" class="eos-btn eos-btn-primary" style="margin-top:8px;"><i class="ti ti-sparkles"></i> Generate Content</button>
</form>

<div id="resultArea" class="eos-card" style="display:none;padding:16px;">
    <div class="eos-card-header" style="margin-bottom:8px;">
        <div class="eos-card-title">Generated Content</div>
        <button type="button" id="copyBtn" class="eos-btn" style="font-size:11px;padding:4px 10px;border:1px solid var(--border);border-radius:6px;background:none;color:var(--text-secondary);cursor:pointer;"><i class="ti ti-copy"></i> Copy</button>
    </div>
    <div id="contentOutput" style="font-size:13px;line-height:1.7;color:var(--text-primary);white-space:pre-wrap;"></div>
</div>

<div id="loadingSpinner" style="display:none;text-align:center;padding:40px;color:var(--text-muted);">
    <i class="ti ti-loader" style="font-size:32px;animation:spin 1s linear infinite;"></i>
    <div style="margin-top:8px;font-size:13px;">Generating content…</div>
</div>

<div id="errorArea" style="display:none;background:#fef2f2;color:#b91c1c;border-radius:8px;padding:12px;margin-top:12px;font-size:13px;"></div>

@push('scripts')
<script>
document.getElementById('aiContentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const tenantId = document.getElementById('tenantSelect').value;
    const type = document.getElementById('typeSelect').value;
    const prompt = document.getElementById('promptInput').value;

    if (!tenantId || !prompt) return;

    document.getElementById('resultArea').style.display = 'none';
    document.getElementById('errorArea').style.display = 'none';
    document.getElementById('loadingSpinner').style.display = 'block';

    try {
        const response = await fetch('{{ route("ai-content.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ tenant_id: tenantId, type, prompt }),
        });

        const data = await response.json();

        document.getElementById('loadingSpinner').style.display = 'none';

        if (data.content) {
            document.getElementById('contentOutput').textContent = data.content;
            document.getElementById('resultArea').style.display = 'block';
        } else if (data.error) {
            document.getElementById('errorArea').textContent = data.error;
            document.getElementById('errorArea').style.display = 'block';
        }
    } catch (err) {
        document.getElementById('loadingSpinner').style.display = 'none';
        document.getElementById('errorArea').textContent = 'Request failed. Check the API configuration.';
        document.getElementById('errorArea').style.display = 'block';
    }
});

document.getElementById('copyBtn').addEventListener('click', function() {
    const content = document.getElementById('contentOutput').textContent;
    navigator.clipboard.writeText(content).then(() => {
        this.innerHTML = '<i class="ti ti-check"></i> Copied';
        setTimeout(() => { this.innerHTML = '<i class="ti ti-copy"></i> Copy'; }, 2000);
    });
});
</script>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endpush
@endsection
