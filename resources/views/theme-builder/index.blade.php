@extends('layouts.app')

@section('title', 'Theme Builder')
@section('subtitle', 'Convert HTML designs into ALOM themes')

@section('content')
<div style="max-width:800px;">
    <div class="eos-card" style="margin-bottom:16px;">
        <div class="eos-card-header">
            <div class="eos-card-title"><i class="ti ti-upload"></i> Upload Design Files</div>
        </div>
        <div class="eos-card-body" style="padding:20px;">
            <form method="POST" action="{{ route('theme-builder.analyze') }}" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div style="border:2px dashed var(--border);border-radius:12px;padding:40px;text-align:center;margin-bottom:20px;" id="dropZone">
                    <i class="ti ti-upload" style="font-size:40px;color:var(--accent-teal);margin-bottom:12px;display:block;"></i>
                    <div style="font-size:14px;font-weight:600;margin-bottom:4px;">Drop files here or click to upload</div>
                    <div style="font-size:11px;color:var(--text-dim);">HTML, CSS, JS, React (ZIP), images — or paste a design URL below</div>
                    <input type="file" name="files[]" multiple accept=".html,.htm,.css,.js,.jsx,.tsx,.zip,.png,.jpg,.jpeg,.svg" style="display:none;" id="fileInput" onchange="updateFileList()">
                </div>
                <div id="fileList" style="font-size:12px;color:var(--text-secondary);margin-bottom:16px;"></div>

                <div class="eos-field">
                    <label class="eos-label">Design URL (optional)</label>
                    <input type="url" name="design_url" class="eos-input" placeholder="https://example.com/template or Google Stitch URL">
                    <div style="font-size:10px;color:var(--text-dim);margin-top:3px;">Paste a URL to an HTML template (e.g. from Google Stitch, HTML5 UP, etc.)</div>
                </div>

                <div class="eos-field">
                    <label class="eos-label">Or Paste HTML Directly</label>
                    <textarea name="paste_html" class="eos-input" rows="6" style="font-family:monospace;font-size:12px;" placeholder="Paste your full HTML code here..."></textarea>
                </div>

                <div class="eos-field">
                    <label class="eos-label">Business Type <span class="text-red-500">*</span></label>
                    <select name="business_type" class="eos-input" required>
                        @foreach ($businessTypes as $key => $type)
                            <option value="{{ $key }}">{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                    <button type="submit" class="eos-btn eos-btn-primary" style="padding:10px 24px;"><i class="ti ti-wand"></i> Analyze Design</button>
                </div>
            </form>
        </div>
    </div>

    @if ($themes->count())
        <div class="eos-card">
            <div class="eos-card-header">
                <div class="eos-card-title"><i class="ti ti-list"></i> Generated Themes</div>
            </div>
            <div class="eos-card-body" style="padding:16px;">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                    @foreach ($themes as $theme)
                        <div style="border:1px solid var(--border);border-radius:8px;padding:12px;background:var(--bg-card);">
                            <div style="font-weight:600;font-size:13px;">{{ $theme->name }}</div>
                            <div style="font-size:10px;color:var(--text-dim);margin:4px 0;">{{ $theme->key }} &middot; {{ $theme->industries[0] ?? '—' }}</div>
                            <div style="display:flex;gap:6px;margin-top:8px;">
                                <a href="{{ route('theme-builder.preview', $theme) }}" class="eos-btn eos-btn-secondary" style="font-size:10px;padding:3px 8px;">Preview</a>
                                <a href="{{ route('theme-builder.download', $theme) }}" class="eos-btn eos-btn-secondary" style="font-size:10px;padding:3px 8px;">Download</a>
                                <form method="POST" action="{{ route('theme-builder.install', $theme) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="eos-btn eos-btn-primary" style="font-size:10px;padding:3px 8px;">Install</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--accent-teal)'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = 'var(--border)'; });
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = 'var(--border)';
    fileInput.files = e.dataTransfer.files;
    updateFileList();
});

function updateFileList() {
    const list = document.getElementById('fileList');
    const files = fileInput.files;
    if (files.length === 0) { list.innerHTML = ''; return; }
    list.innerHTML = Array.from(files).map(f => `<div style="padding:4px 0;"><i class="ti ti-file"></i> ${f.name} (${(f.size/1024).toFixed(1)}KB)</div>`).join('');
}
</script>
@endsection
