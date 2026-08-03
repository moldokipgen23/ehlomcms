@php
    $school = $schoolStats ?? [];
    $schoolLink = fn (string $type) => route('tenant.business-content.index', ['type' => $type]);
@endphp

<style>
    .school-dash { display:grid; gap:18px; }
    .school-hero, .school-panel, .school-stat { background:#fff; border:1px solid var(--border-card); border-radius:12px; box-shadow:0 14px 34px rgba(15,23,42,.055); }
    .school-hero { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:18px; align-items:center; padding:24px; border-top:4px solid #0f766e; }
    .school-kicker { color:#0f766e; font-size:10px; font-weight:900; letter-spacing:1.2px; text-transform:uppercase; }
    .school-title { margin:7px 0 6px; color:var(--text-primary); font:900 28px/1.15 'Syne',sans-serif; }
    .school-copy { max-width:760px; color:var(--text-muted); font-size:13px; line-height:1.6; }
    .school-actions { display:flex; gap:9px; flex-wrap:wrap; justify-content:flex-end; }
    .school-action { display:inline-flex; align-items:center; gap:7px; min-height:38px; padding:0 14px; border-radius:9px; text-decoration:none; font-size:12px; font-weight:900; }
    .school-action.primary { color:#fff; background:#0f766e; box-shadow:0 10px 22px rgba(15,118,110,.18); }
    .school-action.secondary { color:#334155; background:#fff; border:1px solid #d9e2ef; }
    .school-stat-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .school-stat { padding:17px; min-height:112px; position:relative; overflow:hidden; }
    .school-stat:before { content:''; position:absolute; inset:0 0 auto; height:3px; background:var(--school-accent,#0f766e); }
    .school-stat-label { color:var(--text-muted); font-size:10px; font-weight:900; letter-spacing:.7px; text-transform:uppercase; }
    .school-stat-value { margin-top:18px; color:var(--text-primary); font-size:27px; font-weight:900; line-height:1; }
    .school-stat-meta { margin-top:8px; color:var(--text-muted); font-size:11px; }
    .school-main { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(300px,.8fr); gap:14px; }
    .school-panel { padding:18px; }
    .school-panel-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:12px; }
    .school-panel-title { color:var(--text-primary); font-size:15px; font-weight:900; }
    .school-panel-sub { margin-top:3px; color:var(--text-muted); font-size:11px; line-height:1.5; }
    .school-row { display:flex; align-items:center; gap:11px; padding:12px 0; border-bottom:1px solid #edf2f7; }
    .school-row:last-child { border-bottom:0; }
    .school-row-icon { width:34px; height:34px; display:grid; place-items:center; flex:none; border-radius:9px; color:#0f766e; background:#e6f6f3; }
    .school-row-main { min-width:0; flex:1; }
    .school-row-title { color:var(--text-primary); font-size:12.5px; font-weight:900; }
    .school-row-meta { margin-top:3px; overflow:hidden; color:var(--text-muted); font-size:11px; text-overflow:ellipsis; white-space:nowrap; }
    .school-badge { padding:5px 7px; border-radius:5px; color:#047857; background:#ecfdf5; font-size:10px; font-weight:900; }
    .school-tools { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .school-tool { display:flex; align-items:center; gap:9px; min-height:56px; padding:12px; color:var(--text-primary); border:1px solid #e4ebf3; border-radius:9px; text-decoration:none; font-size:12px; font-weight:900; }
    .school-tool i { color:#0f766e; font-size:18px; }
    .school-tool span { display:block; margin-top:3px; color:var(--text-muted); font-size:10px; font-weight:600; }
    .school-checklist { display:grid; gap:9px; }
    .school-check { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 0; border-bottom:1px solid #edf2f7; color:var(--text-muted); font-size:12px; }
    .school-check:last-child { border-bottom:0; }
    .school-check a { color:var(--text-primary); text-decoration:none; font-weight:800; }
    .school-check-status { color:#047857; font-size:10px; font-weight:900; }
    @media(max-width:1000px){.school-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.school-main{grid-template-columns:1fr}}
    @media(max-width:650px){.school-hero{grid-template-columns:1fr;padding:18px}.school-title{font-size:24px}.school-actions{justify-content:flex-start}.school-action{flex:1 1 calc(50% - 5px);justify-content:center;padding:0 10px}.school-stat-grid,.school-tools{grid-template-columns:1fr}}
</style>

<div class="school-dash">
    <section class="school-hero">
        <div>
            <div class="school-kicker">School Website Console</div>
            <div class="school-title">{{ $tenant->name }} overview</div>
            <div class="school-copy">Keep your public school website accurate and welcoming. Publish school information, manage admissions enquiries, share news, and update your campus story from one calm workspace.</div>
        </div>
        <div class="school-actions">
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="school-action primary"><i class="ti ti-external-link"></i> View Website</a>
            <a href="{{ route('tenant.business-inbox') }}" class="school-action secondary"><i class="ti ti-inbox"></i> Enquiries</a>
        </div>
    </section>

    <section class="school-stat-grid">
        <div class="school-stat" style="--school-accent:#0f766e"><div class="school-stat-label">New enquiries</div><div class="school-stat-value">{{ $school['new_enquiries'] ?? 0 }}</div><div class="school-stat-meta">Admission questions to review</div></div>
        <div class="school-stat" style="--school-accent:#2563eb"><div class="school-stat-label">Academic programs</div><div class="school-stat-value">{{ $school['academic_programs'] ?? 0 }}</div><div class="school-stat-meta">Classes and learning stages</div></div>
        <div class="school-stat" style="--school-accent:#f59e0b"><div class="school-stat-label">Faculty & staff</div><div class="school-stat-value">{{ $school['faculty'] ?? 0 }}</div><div class="school-stat-meta">Published team profiles</div></div>
        <div class="school-stat" style="--school-accent:#7c3aed"><div class="school-stat-label">News & notices</div><div class="school-stat-value">{{ $school['notices'] ?? 0 }}</div><div class="school-stat-meta">Current public updates</div></div>
    </section>

    <section class="school-main">
        <div class="school-panel">
            <div class="school-panel-head"><div><div class="school-panel-title">Latest admission enquiries</div><div class="school-panel-sub">New families and questions from the public website.</div></div><a href="{{ route('tenant.business-inbox') }}" class="eos-card-link">View all</a></div>
            @forelse($recentEnquiries as $enquiry)
                <div class="school-row"><div class="school-row-icon"><i class="ti ti-message-2"></i></div><div class="school-row-main"><div class="school-row-title">{{ $enquiry->name }}{{ $enquiry->subject ? ' · ' . $enquiry->subject : '' }}</div><div class="school-row-meta">{{ $enquiry->phone ?: ($enquiry->email ?: 'Contact details not supplied') }} · {{ $enquiry->created_at?->diffForHumans() }}</div></div><span class="school-badge">{{ ucfirst($enquiry->status ?? 'new') }}</span></div>
            @empty
                <div class="eos-empty">No admission enquiries yet. New website submissions will appear here.</div>
            @endforelse
        </div>

        <div class="school-panel">
            <div class="school-panel-head"><div><div class="school-panel-title">Manage your website</div><div class="school-panel-sub">The everyday controls a school team needs.</div></div></div>
            <div class="school-tools">
                <a class="school-tool" href="{{ $schoolLink('academics') }}"><i class="ti ti-book"></i><div>Academics<span>Classes and programs</span></div></a>
                <a class="school-tool" href="{{ $schoolLink('faculty') }}"><i class="ti ti-users"></i><div>Faculty<span>People and profiles</span></div></a>
                <a class="school-tool" href="{{ $schoolLink('notices') }}"><i class="ti ti-news"></i><div>News & Notices<span>Announcements</span></div></a>
                <a class="school-tool" href="{{ route('tenant.content') }}"><i class="ti ti-photo"></i><div>Profile & Gallery<span>About and media</span></div></a>
                <a class="school-tool" href="{{ route('tenant.theme') }}"><i class="ti ti-browser"></i><div>Homepage<span>School website content</span></div></a>
                <a class="school-tool" href="{{ route('tenant.seo') }}"><i class="ti ti-search"></i><div>SEO<span>Search and sharing</span></div></a>
            </div>
        </div>
    </section>

    <section class="school-panel">
        <div class="school-panel-head"><div><div class="school-panel-title">Website readiness</div><div class="school-panel-sub">A quick health check for the public school site.</div></div></div>
        <div class="school-checklist">
            <div class="school-check"><a href="{{ route('tenant.theme') }}"><i class="ti ti-photo"></i> Homepage content</a><span class="school-check-status">{{ filled($tenant->theme_settings['hero_title'] ?? null) || filled($tenant->theme_settings['hero_tagline'] ?? null) ? 'Ready' : 'Needs setup' }}</span></div>
            <div class="school-check"><a href="{{ route('tenant.content') }}"><i class="ti ti-building"></i> School profile</a><span class="school-check-status">{{ filled($tenant->about_text) ? 'Ready' : 'Needs setup' }}</span></div>
            <div class="school-check"><a href="{{ route('tenant.custom-pages') }}"><i class="ti ti-file-plus"></i> Custom pages</a><span class="school-check-status">{{ ($school['published_pages'] ?? 0) > 0 ? 'Ready' : 'Optional' }}</span></div>
            <div class="school-check"><a href="{{ route('tenant.theme') }}?tab=contact"><i class="ti ti-map-pin"></i> Contact and map</a><span class="school-check-status">{{ filled($tenant->contact_address) && filled($tenant->contact_phone) ? 'Ready' : 'Needs setup' }}</span></div>
            <div class="school-check"><a href="{{ route('tenant.theme') }}?tab=admissions"><i class="ti ti-clipboard"></i> Admission information</a><span class="school-check-status">{{ $tenant->hasModule('admissions') ? 'Active' : 'Off' }}</span></div>
            <div class="school-check"><a href="{{ route('tenant.business-inbox') }}"><i class="ti ti-inbox"></i> Admission enquiries</a><span class="school-check-status">{{ $tenant->hasModule('enquiry_form') ? 'Active' : 'Off' }}</span></div>
        </div>
    </section>
</div>
