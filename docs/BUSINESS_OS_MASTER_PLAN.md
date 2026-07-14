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
- **Support Tickets** — MISSING. `tickets` table (tenant_id, subject, messages, status),
  admin list + reply, tenant-side "Support" screen. **Wave 3.**

### PRODUCTS
- **Business Modules** — BUILT (Wave 1, 2026-07-14). Admin page lists every module +
  which business types use it + live tenant counts; config/business_types.php holds the
  default module set per type. (Auto-check on tenant-create form still TODO.)
- **Themes** — BUILT (Info/Shop/Restaurant Classic, Save-as-Template, custom HTML).
- **Add-ons** — PARTIAL, but the GATE now works (Wave 1, 2026-07-14). Tenant::hasActiveAddon()
  is wired to real behavior for the first time: Analytics Pro now actually tracks storefront
  visits + shows a dashboard screen, gated on activation. Pattern proven; the other three
  add-ons (WhatsApp, AI Agent, Email Marketing) still need their real feature builds.
- **Theme Marketplace** — MISSING. Public/browsable theme library + a real Theme SDK
  (theme.zip: theme.json, views/, blocks/, assets/, preview.jpg). **Wave 4.**

### HOSTING
- **Domains** — PARTIAL (manual registrar/expiry records, no automation).
- **Hosting** — PARTIAL (notes only).
- **SSL** — MISSING. Custom-domain connect flow: CNAME instructions → DNS verify →
  Let's Encrypt issue → nginx map. **Wave 3.**
- **Deployments** — MISSING/N-A. No per-tenant deploy in a shared-app model; repurpose
  as a "publish/rollback tenant site" concept only if needed. Low priority.
- **Backups** — PARTIAL. Daily DB backup cron runs (gzip, 14-day). Add per-tenant asset
  backup + an admin Restore action. **Wave 3.**

### FINANCE
- **Invoices** — BUILT.
- **Payments** — PARTIAL. Invoices carry paid/unpaid; no standalone payments ledger.
  **Wave 4.**
- **Expenses** — MISSING. Simple expense records for agency P&L. **Wave 4.**
- **Revenue** — BUILT (Wave 1, 2026-07-14). Finance dashboard: MRR/ARR, active subs + tenants,
  collected vs outstanding, renewals due next 30 days. (Churn still TODO.)

### CONTENT
- **Templates** — PARTIAL (same as Themes today; may stay merged).
- **Media Library** — MISSING. Central admin view of tenant uploads (per-tenant storage
  already exists). **Wave 4.**
- **Email Templates** — PARTIAL. Branded emails exist in code; add an admin editor UI.
  **Wave 4.**

### SYSTEM
- **Users** — MISSING. Manage admin/staff logins. **Wave 3.**
- **Roles** — MISSING. Roles/permissions. BLOCKS School (parent/teacher/student) and
  enables impersonation + staff support. **Wave 3 (before School).**
- **Activity Logs** — PARTIAL. Client-activity log exists; add a full admin audit log
  (who did what, when — e.g. add-on activations, impersonations). **Wave 3.**
- **Settings** — BUILT.
- **System Health** — PARTIAL (error-log viewer; fine for a shared app).

### AI (Future)
- **AI Builder / AI Content / AI Assistant / AI Analytics** — MISSING, correctly last.
  Built on the Theme SDK + module system, gated behind add-on flags. **Wave 5.**

### CLIENT PORTAL (separate, dynamic sidebar — reference)
Every tenant sees the SAME dashboard, only their enabled modules visible. Add per-vertical
"Website" (their storefront link), "Subscription", "Invoices", "Support" items as those
back-end features land. Reservations already added for restaurant tenants.

---

## Build waves (dependency-ordered, not date-ordered)

**Wave 1 — Make it honest & sellable — ✅ DONE 2026-07-14:**
1. ✅ Business Modules admin page + config/business_types.php
2. ✅ Analytics Pro add-on made real (first working hasActiveAddon gate)
3. ✅ Revenue/MRR dashboard
4. ✅ Sidebar regrouped into the target sections

**Wave 2 — Fill the MVP business types:**
4. ✅ DONE 2026-07-14: Portfolio / Business vertical (services, testimonials, blog, gallery + existing content module for about/contact). Scope note: no separate generic "Pages" CMS module was built - the existing Content module already covers a portfolio site's static page needs, and a second module literally named "Pages" would collide with the existing "Content / Pages" module label. A real multi-page builder belongs with the later Theme Engine work, not this MVP vertical.
5. School vertical — AFTER Roles exist (students, teachers, classes, admissions)

**Wave 3 — Operational maturity (System + Hosting + Support):**
6. Users & Roles (unblocks School, impersonation, staff)
7. Client impersonation ("Login as client") + audit log
8. Support Tickets
9. Custom domain + SSL automation
10. Backups: per-tenant assets + Restore UI

**Wave 4 — Content, billing depth, theme platform:**
11. Media Library, Email Templates editor
12. Payments ledger, Expenses
13. Theme SDK (theme.zip) + Theme Marketplace

**Wave 5 — AI (Future):** AI content/assistant/analytics on the SDK + module system.

---

## How every new module/feature stays safe (the rule)

New feature = new nullable columns / new tables (never rewrite tenant data) + gated behind
a flag defaulted off. Ship to everyone (code inert), enable for one test tenant, verify no
other tenant changed, then enable for real buyers. Test migrations against the local
`ehlom_os` DB copy first. This is the shared-codebase substitute for the master doc's
"rebuild image, roll out to 1-2 clients first."
