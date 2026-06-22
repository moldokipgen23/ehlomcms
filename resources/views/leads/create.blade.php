<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Get a Quote — Ehlom Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background:
                radial-gradient(900px 500px at 12% -5%, rgba(79,142,247,.08), transparent 60%),
                radial-gradient(800px 480px at 95% 0%, rgba(139,111,247,.08), transparent 55%),
                var(--bg-base);
            min-height: 100vh;
        }
        .lead-wrap { max-width: 640px; margin: 0 auto; padding: 40px 20px 60px; }
        .lead-head { text-align: center; margin-bottom: 32px; }
        .lead-head h1 { font-size: 24px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; }
        .lead-head p { font-size: 13px; color: var(--text-muted); }
        .lead-logo { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 24px; }
        .lead-logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #4f8ef7, #7c5af7);
            border-radius: 9px; display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700; color: #fff;
        }
        .lead-logo-text { font-size: 16px; font-weight: 700; color: var(--text-primary); letter-spacing: .5px; }
        .lead-logo-sub { font-size: 9px; color: var(--text-dimmer); letter-spacing: 1px; text-transform: uppercase; margin-top: 1px; }
        .lead-card {
            background: linear-gradient(170deg, #1c2135 0%, var(--bg-card) 65%);
            border: 1px solid var(--border-card); border-radius: 12px; padding: 28px 26px;
        }
        .lead-card .eos-form-grid { grid-template-columns: 1fr 1fr; gap: 16px; }
        .lead-card .eos-field.full { grid-column: 1 / -1; }
        .lead-submit { width: 100%; justify-content: center; padding: 12px; font-size: 14px; margin-top: 6px; }
        .lead-foot { text-align: center; margin-top: 20px; font-size: 11px; color: var(--text-dim); }
        .lead-foot a { color: var(--accent-blue); text-decoration: none; }
        @media (max-width: 560px) {
            .lead-wrap { padding: 24px 14px 40px; }
            .lead-card .eos-form-grid { grid-template-columns: 1fr; }
            .lead-card { padding: 20px 16px; }
        }
    </style>
</head>
<body>
    <div class="lead-wrap">
        <div class="lead-logo">
            <div class="lead-logo-icon">E</div>
            <div>
                <div class="lead-logo-text">Ehlom Digital</div>
                <div class="lead-logo-sub">Let's build something great</div>
            </div>
        </div>

        <div class="lead-head">
            <h1>Tell us about your project</h1>
            <p>Fill in the details below and we'll get back to you with a custom quote.</p>
        </div>

        <div class="lead-card">
            <form method="POST" action="{{ route('leads.store') }}">
                @csrf
                <div class="eos-form-grid">
                    <div class="eos-field">
                        <label class="eos-label">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="eos-input" placeholder="Your name" required>
                        @error('name') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Business / Company</label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}" class="eos-input" placeholder="Company name (optional)">
                        @error('business_name') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="eos-input" placeholder="your@email.com">
                        @error('email') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="eos-input" placeholder="+91 9876543210">
                        @error('phone') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eos-field">
                        <label class="eos-label">Project Type *</label>
                        <select name="project_type" class="eos-select" required>
                            <option value="">— Select —</option>
                            @foreach (\App\Models\Lead::PROJECT_TYPES as $val => $label)
                                <option value="{{ $val }}" @selected(old('project_type') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('project_type') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Timeline</label>
                        <select name="timeline" class="eos-select">
                            <option value="">— Select —</option>
                            @foreach (\App\Models\Lead::TIMELINES as $val => $label)
                                <option value="{{ $val }}" @selected(old('timeline') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('timeline') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eos-field">
                        <label class="eos-label">Budget Min (₹)</label>
                        <input type="number" step="0.01" min="0" name="budget_min" value="{{ old('budget_min') }}" class="eos-input" placeholder="Min budget">
                        @error('budget_min') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Budget Max (₹)</label>
                        <input type="number" step="0.01" min="0" name="budget_max" value="{{ old('budget_max') }}" class="eos-input" placeholder="Max budget">
                        @error('budget_max') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eos-field full">
                        <label class="eos-label">Tell us about your project</label>
                        <textarea name="description" class="eos-textarea" placeholder="Describe what you want to build — website, app, features, goals, etc." style="min-height:100px;">{{ old('description') }}</textarea>
                        @error('description') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eos-field full">
                        <label class="eos-label">Specific features / requirements</label>
                        <textarea name="features" class="eos-textarea" placeholder="List any specific features, pages, or functionality you need…" style="min-height:80px;">{{ old('features') }}</textarea>
                        @error('features') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eos-field full">
                        <label class="eos-label">How did you hear about us?</label>
                        <select name="source" class="eos-select">
                            <option value="">— Select —</option>
                            @foreach (\App\Models\Lead::SOURCES as $val => $label)
                                <option value="{{ $val }}" @selected(old('source') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('source') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <button type="submit" class="eos-btn eos-btn-primary lead-submit">
                    <i class="ti ti-send"></i> Send Request
                </button>
            </form>
        </div>

        <div class="lead-foot">
            <a href="/">Ehlom Digital</a> &middot; We'll respond within 24 hours.
        </div>
    </div>
</body>
</html>
