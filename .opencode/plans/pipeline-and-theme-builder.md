# Plan: Pipeline Fix + Theme Builder AI

## Overview

Two major features:
1. **Pipeline Fix** — Connect Leads → Clients → Tenants → Themes → Modules → Domains into one flow
2. **Theme Builder AI** — Upload HTML/React/Figma → ALOM Theme SDK conversion

AI Orchestrator is deferred to last (not in this plan).

---

## Part 1: Pipeline Fix

### Current State (Problems)

| Stage | What Exists | What's Broken |
|-------|------------|---------------|
| Lead → Client | "Convert to Client" button works | Only copies name/email/phone. No business_type, no project_type, no budget, no timeline |
| Client → Tenant | "Create Tenant Site" link works | Opens blank form. No client data flows. Admin must manually re-enter everything |
| Theme Assignment | Theme selection on tenant form | No defaults applied. No preview. No admin edit after creation |
| Module Assignment | Module checkboxes on tenant form | No defaults. No edit after creation. Free/Paid display is confusing |
| Domain Setup | Custom domain + CNAME verify + SSL | **BROKEN** — middleware never reads custom_domain, so custom domains return 404 |
| After Creation | Tenant gets credentials | No onboarding flow. No "what's next" guidance |

### What We'll Build

#### Phase 1: Data Flow (Lead → Client → Tenant)

**1.1 Enhance Lead → Client Conversion**
- File: `app/Http/Controllers/LeadController.php` → `convert()` method
- Currently: copies name, email, phone, wraps description as notes
- Change: also copy lead data → Client columns (all nullable, for traceability)
- Add migration: `alter clients table` — add columns:
  - `project_type` (string, nullable) — from lead's project_type (website, ecommerce, etc.)
  - `budget_min` (decimal 12,2, nullable) — from lead's budget_min
  - `budget_max` (decimal 12,2, nullable) — from lead's budget_max
  - `timeline` (string, nullable) — from lead's timeline (asap, 1month, etc.)
  - `source` (string, nullable) — from lead's source (NOT lead_source; the leads table uses `source`)
  - `features` (text, nullable) — from lead's features
- The `convert()` method copies ALL lead data into the client
- Also copy `description` → Client `notes` (already done, but ensure full copy not truncated)

**1.2 Enhance Client → Tenant Creation**
- File: `resources/views/clients/show.blade.php` — "Create Tenant Site" link already exists (line 93)
- File: `app/Http/Controllers/AdminTenantController.php` → `create()` method
- Currently: `$prefillClient` is passed to the view (line 54) but the Blade template NEVER uses it
- Change: when `client_id` is passed, pre-fill the form with client data via Blade `old()` fallbacks:
  - `name` → from `client->business_name` or `client->name`
  - `contact_email` → from `client->email`
  - `contact_phone` → from `client->phone`
  - `whatsapp_number` → from `client->whatsapp`
  - `about_text` → from `client->notes` (if available)
- **IMPORTANT:** Do NOT auto-map `project_type` → `business_type`. These are different taxonomies:
  - Lead `project_type` = service requested (website, ecommerce, webapp, mobileapp, branding, seo, custom, other)
  - Tenant `site_type` = business vertical (info, shopping, restaurant, business)
  - The admin must choose `site_type` manually during creation (already works via the form dropdown)
- File: `resources/views/tenants/form.blade.php` — add Blade logic to use `$prefillClient` for field defaults:
  - Each field: `old('field', $tenant->field ?? $prefillClient->field ?? '')`
  - Add a flash message: "Pre-filled from client {name}. Review before saving."

**1.3 Auto-Apply Default Theme + Modules**
- File: `app/Http/Controllers/AdminTenantController.php` → `store()` method
- Change: if `template_id` is empty, auto-set to `config('business_types')[$site_type]['template']`
- Change: if `modules` is empty, auto-set to `config('business_types')[$site_type]['default_modules']`
- This ensures every tenant always has a theme and base modules

**1.4 Add Admin Edit Route for Tenants**
- File: `routes/web.php` — add `GET tenants/{tenant}/edit` and `PUT tenants/{tenant}`
- File: `app/Http/Controllers/AdminTenantController.php` — add `edit()` and `update()` methods
- File: `resources/views/tenants/index.blade.php` — add edit button (pencil icon) per row
- This allows admin to change theme, modules, and all settings after creation

#### Phase 2: Custom Domain Fix (CRITICAL)

**2.1 Fix ResolveTenant Middleware**
- File: `app/Http/Middleware/ResolveTenant.php`
- Currently: only resolves by subdomain (`Tenant::where('subdomain', $subdomain)`)
- A request to `shop.client.com` falls through as non-tenant → 404
- Change: after the `str_ends_with` check on line 23 fails, add custom domain lookup:
  ```php
  // After line 23, before returning:
  $tenant = Tenant::where('custom_domain', $host)
      ->where('domain_status', 'verified')
      ->first();
  if ($tenant) {
      URL::defaults(['subdomain' => $tenant->subdomain]);
      app(TenantContext::class)->set($tenant);
      return $next($request);
  }
  ```
- This makes custom domains actually serve the tenant's site

**2.2 Fix Tenant Route Domain Constraint**
- File: `routes/tenant.php` line 50
- Currently: `Route::domain('{subdomain}.' . config('app.tenant_domain', 'ehlom.com'))` — only matches `*.ehlom.com`
- Change: add a second route group for custom domains, OR remove the domain constraint and rely on ResolveTenant middleware
- Recommended approach: remove domain constraint, keep subdomain param via URL::defaults

**2.3 URL Rewriting for Custom Domains**
- When ResolveTenant resolves via custom_domain, all `route()` calls generate `*.ehlom.com` URLs
- Fix: after resolving custom domain, call `URL::forceRootUrl("https://{$host}")` and `URL::forceScheme('https')`
- This ensures links, forms, and CSRF tokens work on the custom domain

**2.4 CNAME Instructions UI**
- File: `resources/views/domains/admin-index.blade.php`
- Current instruction text is vague: "Point a CNAME record from the custom domain to ehlom.com"
- Change: show clear step-by-step:
  ```
  1. Log in to your domain registrar (GoDaddy, Namecheap, etc.)
  2. Go to DNS Management for your domain
  3. Add a CNAME Record:
     - Host/Name: @ (or shop if using shop.client.com)
     - Value/Target: portal.ehlom.com
     - TTL: 300 (5 minutes)
  4. Wait 5-30 minutes for DNS propagation
  5. Click "Verify" below
  ```
- Add "Copy CNAME Target" button that copies `portal.ehlom.com` to clipboard

**2.5 SSL Auto-Renewal via Cron**
- Currently: SSL renewal requires admin to click "Renew SSL" button
- Add: a cron job or artisan command that checks all verified custom domains and renews certs nearing expiry
- File: `app/Console/Commands/RenewSslCertificates.php` (new)
- Schedule: daily via `routes/console.php`

**2.6 Custom Domain Middleware Registration**
- Ensure `ResolveTenant` middleware runs on ALL requests (already prepended globally in `bootstrap/app.php` line 18)
- Verify that custom domain requests are not blocked by CloudPanel/nginx before reaching Laravel

#### Phase 3: Onboarding Wizard

**3.1 Build Onboarding Controller**
- New file: `app/Http/Controllers/AdminOnboardingController.php`
- Add migration: `alter tenants table` — add `onboarding_step` (string, nullable, default null) column
  - Values: null (not started), 'info', 'theme', 'modules', 'domain', 'done'
- Flow: after tenant is created, redirect to onboarding instead of tenant list
- Steps: 1) Confirm Business Info → 2) Select Theme (with preview) → 3) Configure Modules → 4) Setup Domain → 5) Done
- Each step saves progress to `onboarding_step` column on the tenant
- Onboarding is SKIPPABLE: admin can click "Skip to Dashboard" at any step
- When `onboarding_step` = 'done' or null, tenant list shows normally (no redirect)
- Middleware: use existing `admin.role` middleware (no new middleware needed)

**3.2 Onboarding Views**
- New files: `resources/views/onboarding/step-1-info.blade.php` through `step-5-done.blade.php`
- Step 1: Pre-filled client info (name, email, phone, business type) — admin confirms/edits
- Step 2: Theme gallery with LIVE PREVIEW (opens tenant site in iframe in new tab)
- Step 3: Module toggles with descriptions
- Step 4: Domain setup — shows CNAME instructions, verify button, SSL button
- Step 5: Success — shows credentials, "Visit Site" button, "Go to Dashboard" button

**3.3 Domain Setup Instructions**
- On Step 4, show clear instructions:
  ```
  1. Go to your domain registrar (GoDaddy, Namecheap, etc.)
  2. Add a CNAME record:
     - Name: @ (or subdomain)
     - Value: portal.ehlom.com
  3. Wait 5-30 minutes for propagation
  4. Click "Verify" below
  ```
- Show a "Copy CNAME Value" button
- Auto-verify after set (if DNS is already propagated)

#### Phase 4: Theme Preview

**4.1 Live Theme Preview**
- New route: `GET themes/{theme}/preview` → `AdminThemeController@preview`
- Opens the theme in a new tab with demo data (sample products, about text, etc.)
- Uses the theme's `base_template` or `custom_html` with placeholder content
- Admin can preview before assigning to tenant

**4.2 Theme Preview on Tenant Form**
- On the tenant creation/edit form, add a "Preview" link next to each theme card
- Opens preview in new tab

---

## Part 2: Theme Builder AI

### What It Does

Input: HTML/CSS/JS files, React components, Figma URL, or screenshot
Output: Installable ALOM Theme SDK package (Blade templates + components + config + manifest + preview image)

### Existing Services (for reference)

The codebase already has these services in `app/Services/`:
- `CustomThemeRenderer.php` — renders tenant storefront themes with custom HTML/settings
- `InvoiceService.php` — invoice business logic
- `MailConfigService.php` — configures mail transport from settings
- `TenantContext.php` — resolves and holds the current tenant
- `NotificationService.php` — generates notification alerts
- `ErrorLogReader.php` — reads Laravel error logs

The Theme Builder services (ThemeAnalyzer, ThemeGenerator, ThemePackager) are NEW and will be added alongside these.

### Theme SDK Documentation

`docs/THEME_SDK.md` does NOT exist yet. It must be created as a prerequisite for the Theme Generator AI. The docs will cover:
- Blade template structure (how themes map to `resources/views/tenant-templates/`)
- Component API (reusable blocks, sections, partials)
- `theme.json` schema (manifest with name, version, author, settings, screenshots)
- Asset handling (CSS, JS, images in `public/`)
- Responsive patterns (mobile-first, breakpoints)
- Integration with `TenantHomeController` (how `base_template` and `custom_html` are resolved)
- This doc is fed as system context to the AI during theme generation

### Architecture

```
User uploads files
       ↓
Theme Builder AI Controller
       ↓
1. Parse input (HTML extraction, React analysis, Figma API, screenshot OCR)
       ↓
2. AI Analysis (send to OpenAI/Anthropic with Theme SDK docs)
       ↓
3. Generate Theme SDK output:
   - theme.json (manifest)
   - views/{theme-key}/index.blade.php (main template)
   - views/{theme-key}/partials/ (header, footer, sections)
   - components/ (reusable blocks)
   - assets/ (CSS, JS, images)
   - README.md
   - preview.png (generated from HTML)
       ↓
4. Package as ZIP
       ↓
5. Save to themes table + storage
```

### Files to Build

#### Phase 1: Core Theme Builder

**1.1 Theme Builder Controller**
- New file: `app/Http/Controllers/AdminThemeBuilderController.php`
- Methods:
  - `index()` — upload form
  - `analyze(Request $request)` — parse uploaded files, send to AI, return analysis
  - `generate(Request $request)` — generate theme from analysis
  - `preview($themeId)` — preview generated theme
  - `install($themeId)` — install to themes table
  - `download($themeId)` — download as ZIP

**1.2 Theme Analyzer Service**
- New file: `app/Services/ThemeAnalyzer.php`
- Parses HTML: extracts hero, header, footer, cards, buttons, sections, animations
- Parses React: extracts component tree, props, styles
- Handles Figma: uses Figma REST API to extract frames, styles, text
- Handles screenshot: sends to vision model for layout analysis
- Returns structured analysis JSON

**1.3 Theme Generator Service**
- New file: `app/Services/ThemeGenerator.php`
- Takes analysis + Theme SDK documentation as context
- Calls AI (OpenAI GPT-4o or Claude) to generate:
  - Blade templates with proper ALOM blocks
  - Component definitions
  - Theme config (colors, fonts, settings)
  - Manifest (theme.json)
- Uses streaming for real-time progress

**1.4 Theme Packager Service**
- New file: `app/Services/ThemePackager.php`
- Takes generated theme files
- Creates ZIP with:
  - `theme.json` (manifest)
  - `views/` directory with Blade files
  - `assets/` directory (CSS, JS, images extracted from HTML)
  - `README.md` with installation instructions
  - `preview.png` (screenshot or generated preview)
- Saves ZIP to `storage/app/themes/`

#### Phase 2: Theme Builder UI

**2.1 Upload Form**
- New file: `resources/views/theme-builder/index.blade.php`
- Drag-and-drop zone for HTML/CSS/JS files
- React file upload (zip or individual files)
- Figma URL input field
- Screenshot upload
- Business type selector (info, shopping, restaurant, business)
- Theme name input

**2.2 Analysis Results**
- New file: `resources/views/theme-builder/analysis.blade.php`
- Shows what AI found: sections, components, colors, fonts, layout
- Admin can adjust before generating

**2.3 Generation Progress**
- New file: `resources/views/theme-builder/generating.blade.php`
- Real-time progress via SSE or polling
- Shows: Analyzing → Generating Blade → Creating Components → Building Assets → Packaging ZIP

**2.4 Theme Preview**
- New file: `resources/views/theme-builder/preview.blade.php`
- Live preview of generated theme in iframe
- "Install to Marketplace" button
- "Download ZIP" button
- "Edit Theme" link

#### Phase 3: Routes + Integration

**3.1 Routes**
- `GET /theme-builder` → `AdminThemeBuilderController@index`
- `POST /theme-builder/analyze` → `AdminThemeBuilderController@analyze`
- `POST /theme-builder/generate` → `AdminThemeBuilderController@generate`
- `GET /theme-builder/{theme}/preview` → `AdminThemeBuilderController@preview`
- `POST /theme-builder/{theme}/install` → `AdminThemeBuilderController@install`
- `GET /theme-builder/{theme}/download` → `AdminThemeBuilderController@download`

**3.2 Sidebar Addition**
- Add "Theme Builder" to sidebar under "Products" section (after "Themes")
- Badge: count of themes generated this month

**3.3 Theme SDK Documentation**
- New file: `docs/THEME_SDK.md` — comprehensive docs for the AI to reference
- Covers: Blade template structure, component API, block system, theme.json schema, asset handling, responsive patterns
- This is critical — the AI needs to understand the SDK to generate valid themes

---

## Implementation Order

### Sprint 1: Pipeline Fix + Domain Fix (Days 1-4)
1. Migration: add columns to clients table (project_type, budget_min, budget_max, timeline, source, features)
2. Migration: add onboarding_step column to tenants table
3. Update LeadController@convert to copy all lead data (project_type, budget_min, budget_max, timeline, source, features)
4. Update tenants/form.blade.php to use $prefillClient for field defaults
5. Update AdminTenantController@store to auto-apply default theme+modules from config business_types
6. Add AdminTenantController@edit + update methods
7. Add edit route + edit button on tenant list
8. **Fix ResolveTenant middleware** to resolve custom domains via `Tenant::where('custom_domain', $host)`
9. **Fix tenant routes** to work with custom domains (remove or relax domain constraint)
10. **Add URL rewriting** for custom domain requests (forceRootUrl, forceScheme)
11. **Update CNAME instructions** UI with step-by-step guide + copy button
12. **Add SSL auto-renewal cron** command

### Sprint 2: Onboarding Wizard (Days 5-7)
1. Create AdminOnboardingController with step logic
2. Create onboarding blade views (5 steps)
3. Add onboarding routes
4. Redirect tenant creation to onboarding
5. Add domain setup instructions with CNAME copy button

### Sprint 3: Theme Preview (Day 8)
1. Add preview route to AdminThemeController
2. Build preview page with demo data
3. Add preview links to tenant form theme cards

### Sprint 4: Theme Builder AI - Core (Days 9-13)
1. ThemeAnalyzer service (HTML/React parsing)
2. ThemeGenerator service (AI generation)
3. ThemePackager service (ZIP creation)
4. Theme SDK documentation

### Sprint 5: Theme Builder AI - UI (Days 14-16)
1. Upload form with drag-drop
2. Analysis results page
3. Generation progress page
4. Preview + install page
5. Sidebar integration

### Sprint 6: Polish + Deploy (Day 17)
1. Test full pipeline flow
2. Test theme builder with sample HTML
3. Fix bugs, clean up
4. Deploy to VPS

---

## Key Files to Modify

| File | Change |
|------|--------|
| `database/migrations/` | New migration: add columns to clients (project_type, budget_min, budget_max, timeline, source, features) |
| `database/migrations/` | New migration: add onboarding_step to tenants |
| `app/Http/Middleware/ResolveTenant.php` | Add custom_domain lookup after subdomain check |
| `routes/tenant.php` | Remove/relax domain constraint for custom domains |
| `app/Http/Controllers/LeadController.php` | Enhance `convert()` to copy all lead data |
| `app/Http/Controllers/AdminTenantController.php` | Add `edit()`, `update()`, enhance `store()` with defaults |
| `app/Http/Controllers/AdminThemeController.php` | Add `preview()` method |
| `app/Http/Controllers/AdminDomainController.php` | Update CNAME instructions, add URL rewriting |
| `app/Console/Commands/RenewSslCertificates.php` | New: daily SSL renewal cron |
| `routes/web.php` | Add tenant edit routes, onboarding routes, theme builder routes, theme preview route |
| `resources/views/layouts/app.blade.php` | Add "Theme Builder" to sidebar |
| `resources/views/tenants/form.blade.php` | Use $prefillClient for defaults, add preview links |
| `resources/views/tenants/index.blade.php` | Add edit button |
| `resources/views/domains/admin-index.blade.php` | Step-by-step CNAME instructions + copy button |

## New Files to Create

| File | Purpose |
|------|---------|
| `app/Console/Commands/RenewSslCertificates.php` | Daily SSL renewal cron for custom domains |
| `app/Http/Controllers/AdminOnboardingController.php` | Onboarding wizard |
| `app/Http/Controllers/AdminThemeBuilderController.php` | Theme Builder AI |
| `app/Services/ThemeAnalyzer.php` | Parse uploaded designs |
| `app/Services/ThemeGenerator.php` | AI theme generation |
| `app/Services/ThemePackager.php` | ZIP packaging |
| `resources/views/onboarding/step-1-info.blade.php` | Confirm business info |
| `resources/views/onboarding/step-2-theme.blade.php` | Select theme with preview |
| `resources/views/onboarding/step-3-modules.blade.php` | Configure modules |
| `resources/views/onboarding/step-4-domain.blade.php` | Domain setup with CNAME instructions |
| `resources/views/onboarding/step-5-done.blade.php` | Success + credentials |
| `resources/views/theme-builder/index.blade.php` | Upload form |
| `resources/views/theme-builder/analysis.blade.php` | AI analysis results |
| `resources/views/theme-builder/generating.blade.php` | Generation progress |
| `resources/views/theme-builder/preview.blade.php` | Theme preview + install |
| `docs/THEME_SDK.md` | Theme SDK documentation for AI |
