# Ehlom Business OS — Master Plan (sidebar-driven)

This is the canonical roadmap. It supersedes the *phase-numbered* framing in the other
docs for planning purposes — those stay as historical build logs. Here the **admin
sidebar itself is the roadmap**: the target Super Admin sidebar has seven sections, and
"finishing the product" means filling every item in them. Each item below is tagged
**BUILT / PARTIAL / MISSING** against the real code as of 2026-07-14, with a one-line
scope and the wave it's scheduled in.

Grounding facts (do not re-litigate):
- **Architecture:** one shared codebase, one shared DB, `tenant_id`-scoped rows,
  per-tenant storage folders. NOT Docker-per-tenant, NOT database-per-tenant. See the
  reasoning already recorded — this is settled.
- **Two portals, two sidebars:**
  - **Admin portal** (`portal.ehlom.com`) — the agency cockpit. FIXED sidebar (this doc).
  - **Client portal** (`{sub}.ehlom.com/dashboard`) — each tenant's own dashboard.
    DYNAMIC sidebar: shows only that tenant's enabled modules. Already works this way.
- **Work method:** edit + commit directly on the VPS as `ehlom-portal`; never push to
  GitHub unless asked. Test each item on production against a throwaway tenant, then
  delete the test data.

---

## Naming reconciliation (decide once, early)

The vision sidebar says **"Clients"** under CLIENT MANAGEMENT. The current code has TWO
distinct concepts:
- `Client` — an agency CRM record (may have no live site; the old manual clients).
- `Tenant` — a live client site on a subdomain (the SaaS product).

A `Client` can optionally own a `Tenant`. The vision's "Create Client → business type →
deploy → client.alom.com" flow = our **Tenant** creation. Recommendation: keep both
tables, but in the *admin sidebar* present "Clients" as the CRM list and keep "Tenants"
as the live-sites list (or later merge the UI so one screen shows a client + their site).
Flagged here so we don't silently collapse two real tables.

---

## Target Super Admin sidebar (status + scope + wave)

### DASHBOARD
- **Dashboard** — BUILT. Real counts (clients, projects, invoices, leads), alerts, recent
  invoices. Later add cross-tenant MRR tile (see Finance → Revenue).

### CLIENT MANAGEMENT
- **Clients** — BUILT.
- **Subscriptions** — BUILT (currently under Finance; move here).
- **Agreements** — BUILT (currently under Work; move here).
- **Projects** — BUILT (currently under Work; move here).
- **Support Tickets** — BUILT (Wave 3, 2026-07-14). `tenant_tickets` + `ticket_replies`
  tables, admin list/reply/close, tenant-side ticket screen.

### PRODUCTS
- **Business Modules** — BUILT (Wave 1, 2026-07-14). Admin page lists every module +
  which business types use it + live tenant counts. Free-module assignment per business
  type became a real admin-editable DB setting (`business_type_modules` table, tick
  boxes on the page itself) on 2026-07-14 — no longer a config file only a developer
  could edit. Tenant-create form auto-ticks defaults from the same table.
- **Themes** — BUILT (Info/Shop/Restaurant/Business Classic, Save-as-Template, custom HTML).
- **Add-ons** — BUILT mechanism, PARTIAL coverage. `Tenant::hasActiveAddon()` gate proven
  real (Analytics Pro, Wave 1). Card-grid marketplace with per-business-type tagging and
  live activation stats (Wave 1.5). WhatsApp Automation and Email Marketing still need
  their real feature builds; AI Agent effectively arrived early via the Wave 3/4 AI
  Content/Assistant work (see AI section below), though not yet wired as *the* AI Agent
  add-on specifically.
- **Theme Marketplace** — BUILT (Wave 4, 2026-07-14). Public theme browsing page +
  theme.zip / theme.json SDK export from any theme.

### HOSTING
- **Domains** — PARTIAL (manual registrar/expiry records for the old agency CRM
  clients, no automation — this is a *different* feature from Custom Domains below).
- **Hosting** — BUILT (Wave 4, 2026-07-14). Hosting plan catalog (admin-managed).
- **SSL** — BUILT (Wave 3, 2026-07-14). Full flow: admin sets a tenant's custom domain →
  CNAME verify (DNS lookup) → certbot issues/renews via Let's Encrypt. Verified
  end-to-end on production with a throwaway tenant (domain set → saved as pending →
  visible in admin list). The initial version had a command-injection vulnerability in
  the certbot exec() calls (tenant domain string interpolated unescaped) — fixed same
  day with escapeshellarg() + a strict domain-format allowlist regex, tested against 14
  cases including real injection payloads before deploy.
- **Deployments** — MISSING/N-A. No per-tenant deploy in a shared-app model; repurpose
  as a "publish/rollback tenant site" concept only if needed. Low priority.
- **Backups** — BUILT (Wave 3, 2026-07-14). Daily DB backup cron (existing) + admin
  Backups page: manual DB backup trigger, DB restore (admin-only, gated), per-tenant
  asset zip backup/restore/download. Note: deleting a user who has any audit log entry
  currently fails on a hard FK constraint (`audit_logs.user_id` has no cascade/null-on-
  delete) — not a security issue, but worth a small follow-up migration later since it
  will surface as a confusing error the first time someone tries to delete an active
  user or tenant owner from the Users screen.

### FINANCE
- **Invoices** — BUILT.
- **Payments** — BUILT (Wave 4, 2026-07-14). Standalone payments ledger, admin CRUD.
- **Expenses** — BUILT (Wave 4, 2026-07-14). Agency expense records, admin CRUD.
- **Revenue** — BUILT (Wave 1, 2026-07-14). Finance dashboard: MRR/ARR, active subs + tenants,
  collected vs outstanding, renewals due next 30 days. (Churn still TODO.)

### CONTENT
- **Templates** — PARTIAL (same as Themes today; may stay merged).
- **Media Library** — BUILT (Wave 4, 2026-07-14). Central admin view of tenant uploads.
- **Email Templates** — BUILT (Wave 4, 2026-07-14). Admin CRUD + editor for branded
  system emails.

### SYSTEM
- **Users** — BUILT (Wave 3, 2026-07-14). Admin CRUD for agency users, assigns a Role.
- **Roles** — BUILT (Wave 3, 2026-07-14), enforced on the genuinely sensitive actions
  only (not the whole panel, by deliberate scoping — see below): `roles` table
  (admin/staff), `admin.role` middleware applied to Users management, impersonation,
  backup restore, and domain removal/SSL actions. Verified on production: a real
  staff-role test user got a 403 on Users management while normal dashboard access still
  worked; the existing admin account was backfilled to the `admin` role by the migration
  itself so nobody got locked out. Unblocks School.
- **Activity Logs** — now **Audit Logs**, BUILT (Wave 3, 2026-07-14). Full admin action
  log (impersonation start/end, SSL issue/renew, domain verify/remove, backup run/
  restore, etc.), not just client activity.
- **Settings** — BUILT.
- **System Health** — PARTIAL (error-log viewer, now also cache-clear and migrate
  actions from the same page).

### AI (Future) — arrived earlier than planned, Wave 3/4 batch
- **AI Content** — BUILT (2026-07-14). Per-tenant AI settings (provider: OpenAI or
  Anthropic, API key **encrypted at rest**, matching the existing PaymentSetting
  pattern), admin content-generation tool (about us / product description / blog post /
  service description) with real API calls.
- **AI Assistant** — BUILT (2026-07-14). Public storefront chat widget, gated per-tenant
  (`assistant_enabled`, invisible unless configured — confirmed existing live tenants
  render identically, zero visual change), real API calls with the tenant's own key.
- **AI Builder / AI Analytics** — still MISSING, correctly deferred.

### CLIENT PORTAL (separate, dynamic sidebar — reference)
Every tenant sees the SAME dashboard, only their enabled modules visible. Add per-vertical
"Website" (their storefront link), "Subscription", "Invoices" items as those back-end
features land. Reservations (restaurant), Services/Testimonials/Blog (business), and
Support Tickets are already tenant-side screens.

---

## Build waves (dependency-ordered, not date-ordered)

**Wave 1 — Make it honest & sellable — ✅ DONE 2026-07-14:**
1. ✅ Business Modules admin page + config/business_types.php
2. ✅ Analytics Pro add-on made real (first working hasActiveAddon gate)
3. ✅ Revenue/MRR dashboard
4. ✅ Sidebar regrouped into the target sections
5. ✅ (follow-up) Card-grid Add-on Marketplace, per-business-type Free/Paid split,
   business_type_modules made DB-editable, "Products" naming collision fixed

**Wave 2 — Fill the MVP business types:**
4. ✅ DONE 2026-07-14: Portfolio / Business vertical (services, testimonials, blog, gallery + existing content module for about/contact).
5. School vertical — still pending, now unblocked by Wave 3's Roles work. Next up.

**Wave 3 — Operational maturity — ✅ DONE 2026-07-14 (built by a second AI agent,
reviewed and fixed before deploy — see below):**
6. ✅ Users & Roles
7. ✅ Client impersonation + audit log
8. ✅ Support Tickets
9. ✅ Custom domain + SSL automation
10. ✅ Backups: per-tenant assets + Restore UI

**Wave 4 — Content, billing depth, theme platform — ✅ DONE 2026-07-14 (same batch):**
11. ✅ Media Library, Email Templates editor
12. ✅ Payments ledger, Expenses, Hosting plan catalog
13. ✅ Theme SDK (theme.zip) + Theme Marketplace

**Wave 5 — AI:** Content generation + storefront assistant landed early as part of the
Wave 3/4 batch (see AI section above). AI Builder / Analytics still not started.

### Wave 3/4 delivery note (2026-07-14)

This batch was built by a second AI coding agent working from a local, uncommitted
checkout, then reviewed and fixed here before anything touched the VPS or GitHub. Five
real issues were found and fixed pre-deploy:
1. **Critical: command injection** in the SSL exec() calls (tenant domain string,
   unescaped) — fixed with escapeshellarg() + a strict domain-format allowlist,
   verified against real injection payloads.
2. **Custom Domains was unusable** — no form anywhere set a domain in the first place.
   Added the missing set-domain action.
3. **Impersonation didn't actually work** — same-session `auth()->login()` across two
   different subdomains doesn't survive host-only session cookies. Rebuilt as a signed,
   single-use, 2-minute cross-domain handoff (`URL::temporarySignedRoute`). Verified for
   real: enter → lands on the actual tenant dashboard (not its login page) → leave →
   back on the admin panel with the original session untouched.
4. **Roles were decorative** — the middleware existed but was applied nowhere. Applied
   it to the genuinely sensitive actions only (Users, impersonation, backup restore,
   domain removal/SSL) and added a migration-level backfill so the existing admin
   account wasn't locked out. Verified: staff test account got 403'd correctly, admin
   account unaffected.
5. A migration silently altered an unrelated table under a misleading name — split into
   two accurately-named migrations.

Everything was then lint-checked, migrated fresh against a local DB, route-list-verified
(239 routes, correct middleware confirmed per route), then deployed to the VPS the same
way as every other change this session: migrate --force on production, then live HTTP
verification with throwaway test data, then cleanup.

**Follow-up functional pass (2026-07-15):** the above was thorough code review + safe
non-destructive testing, but a direct question ("is it 100% working?") prompted actually
clicking through every remaining untested screen with real form submissions - not just
loading each page and checking for 200. This found 2 more real bugs neither lint nor
route:list could catch, both now fixed and verified live:
- Hosting Plans and Email Templates 500'd on save whenever their optional JSON field
  (features / variables) was left blank - `$validated['field']` was accessed directly,
  but Laravel's validate() omits an absent nullable field from the array entirely rather
  than setting it to null, so direct access threw "Undefined array key". Fixed with
  `empty()` (verified this doesn't dereference a missing key, unlike direct [] access).
- The tenant-side ticket detail page 500'd for any real ticket - `TenantTicketController::
  show()` used Eloquent implicit route-model binding, which this codebase's own existing
  comments (TenantCartController) already document as broken on domain-scoped tenant
  routes. Fixed with the same plain-param + tenant-scoped findOrFail() pattern used
  everywhere else in this controller family.

Also fixed same-day: the naming collision between "Info / Portfolio" and "Portfolio /
Business" (both said Portfolio) - renamed to "Info / Basic", plus the Info Classic theme
description no longer says "portfolio sites" either.

All 4 business types (Info/Basic, Shopping, Restaurant, Portfolio/Business) verified
end-to-end same day with a fresh throwaway tenant per type: correct storefront content,
zero cross-contamination between verticals, correct dashboard sidebar per type.

**Honest state after this pass:** every admin screen in Wave 1-4 has now been either
proven with a real write (Users, Roles, Impersonation, Domains/SSL-set, Backups run,
Business Modules, Add-ons, Revenue, Support Tickets, Expenses, Payments, Hosting Plans,
Email Templates, Analytics) or confirmed to load/render correctly (Theme Marketplace,
Media Library, Audit Log, AI Settings/Content pages - these last few still need a real
OpenAI/Anthropic API key to prove the actual AI calls, and actual certbot SSL issuance
still needs a domain with real DNS pointed at this server to fully prove - both are
architecturally sound and reviewed, just not fireable with fake test data).

---

## How every new module/feature stays safe (the rule)

New feature = new nullable columns / new tables (never rewrite tenant data) + gated behind
a flag defaulted off. Ship to everyone (code inert), enable for one test tenant, verify no
other tenant changed, then enable for real buyers. Test migrations against the local
`ehlom_os` DB copy first. This is the shared-codebase substitute for the master doc's
"rebuild image, roll out to 1-2 clients first."


### Data-model wiring pass (2026-07-15)

User spotted duplicate-looking tags on the Theme Marketplace and asked whether
Client/Tenant, Themes/Theme Marketplace, and Domains/Hosting were actually connected.
Investigated with real production data rather than guessing:

- **Client <-> Tenant**: mechanism already existed and works correctly (verified live
  with throwaway data - client_id saves, Client page shows the linked tenant). Real
  production state: 2 real Clients (Ngamboi, Dora), 2 real Tenants (both literally named
  "Demo Shop"/"My Test Shop" - showcase data, not real client sites) - currently 0 linked
  pairs, correctly so, since force-linking demo tenants to real clients would be wrong.
- **Theme Marketplace duplicate tags**: fixed - was rendering base_template (raw key)
  and industries (also raw keys) as two separate badge rows; for 3 of 4 themes those
  are literally identical text. Now shows one badge per business type in its real label.
- **Hosting Plans had zero attachment mechanism** - confirmed no foreign key pointed at
  it from anywhere. Added tenants.hosting_plan_id (nullOnDelete), wired into the Tenants
  list (inline assignable select) and Tenant create form, Hosting Plans page now shows
  real tenant counts per plan. Verified live: created a real plan, assigned it to a
  tenant, confirmed it showed correctly in both directions, then cleaned up.
- **A third naming collision found**: the OLD Domains & Hosting page (Client-linked
  manual billing) has its own "Hosting Plans" tab - but that's actually Product records
  (Service Catalog, category=hosting), unrelated to the new HostingPlan model. Renamed
  to "Hosting Pricing" and added cross-reference notes on both pages.
- **Cross-linked the two domain systems**: Client show page now displays the linked
  Tenant's custom_domain/SSL status inline (if set) with a link to Custom Domains admin;
  Custom Domains admin page now shows a Client column linking back. Verified live in
  both directions with a throwaway Client+Tenant pair, then cleaned up.

**Still genuinely separate, not merged, by design**: `Tenant.plan` (legacy free-text
label, predates HostingPlan, kept for backward compat) vs `HostingPlan` (new structured
catalog) vs `Subscription` (old CRM recurring billing, tied to Client) - three real
billing concepts that are NOT the same thing and weren't unified in this pass. Worth a
dedicated look before any actual payment automation is built on top of them.

### Themes / Theme Marketplace merge + real ZIP upload (2026-07-15)

User directly asked why Themes and Theme Marketplace were two pages and pushed back hard
on it (rightly - it traced to the original vision doc listing them separately, but they
were genuinely indistinguishable in practice since every theme was public). Per explicit
request: merged into one Themes page, cards grouped by business type (same visual
pattern as Business Modules), Theme Marketplace controller/view/route/sidebar link
removed entirely.

Added the ZIP upload that was missing (only export/download existed before, no import).
Reads theme.json + custom.html from the uploaded zip entirely in-memory
(ZipArchive::getFromName()) - never extracts arbitrary files to disk, never executes
anything from the zip. The security boundary: an uploaded zip can only ever become
inert template text (the theme's custom_html field), rendered through the existing
CustomThemeRenderer token-substitution path - the same as pasting HTML by hand.

Two more real bugs found and fixed during this pass:
- The theme create form's "Suited For" checkboxes were hardcoded to only Shopping/Info -
  Restaurant and Portfolio/Business (both added earlier this session) were never
  selectable for a custom theme. Now loops config('business_types').
- Introduced and immediately caught a Blade 500: wrote a literal token example inside
  the view's own {{ }} echo syntax, which this codebase's own code comments already
  document as breaking Blade's compiler. Fixed by reusing the existing tokenDocs()
  controller-built string instead of writing the token literally in the view.

Verified fully live: built a real theme.zip (theme.json + custom.html with a unique
marker string), uploaded it via a real multipart POST, confirmed it appeared under both
tagged business types, assigned it to a throwaway tenant, and confirmed the storefront
rendered the actual uploaded HTML with tenant name/about correctly substituted - not a
cached fallback. Test tenant and theme deleted after. Regression-checked demoshop and
testshop still return 200.