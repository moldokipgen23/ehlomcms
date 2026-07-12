# Standalone phase prompts

Each prompt below is self-contained — paste ONE at a time into a fresh AI coding session
(DeepSeek, Nemotron, etc). Do not paste more than one phase per session. Wait for a human
(or a stronger model) to review the diff before starting the next phase — the guardrails
repeated in every prompt are there because a weaker/free model may otherwise take shortcuts
that break tenant data isolation or leak payment credentials.

Do these in order: 0 → 1 → 2 → 3 → 4 → 5 → 6 → 7. Do not skip ahead.

---

## Phase 0 — Infra (manual, not a coding prompt)

Not for an AI agent. Human does this:
1. Move `portal.ehlom.com` from Bunny Docker to the VPS (CloudPanel).
2. Add wildcard DNS: `*.ehlom.com` → VPS IP.
3. Configure CloudPanel vhost with `*.ehlom.com` alias/wildcard server_name.
4. Issue wildcard Let's Encrypt cert for `*.ehlom.com` via DNS-01.
5. Confirm `portal.ehlom.com` works identically to before on the new host.

Do not start Phase 1 until this is done and verified.

---

## Phase 1 — Tenant foundation

```
This is a Laravel 13 app called ehlom-os. It is currently a single-tenant internal CRM
for a web agency: it has `Client`, `Project`, `Invoice`, `Subscription`, `Domain`,
`Agreement`, `Lead` models, and single admin auth via Laravel Breeze. There is no tenant
concept yet.

GUARDRAILS (read before writing any code):
- Do NOT modify, rename, or repurpose the existing `clients` table or the `Client` model.
  It is the agency's own CRM data. Existing code (Project, Invoice, Subscription, Domain,
  Agreement all belongsTo Client) depends on it unchanged.
- Do NOT install stancl/tenancy or any multi-database tenancy package. Use single-database
  multi-tenancy: one `tenant_id` column + Eloquent global scopes.
- This phase is data-layer only. Do not touch routes, controllers, or views.

TASK:
1. Create a migration for a new `tenants` table: `client_id` (nullable, foreign key to
   `clients`, nullable because not every tenant maps to an existing CRM client), `subdomain`
   (string, unique, not nullable), `name` (string), `site_type` (string, default 'info' —
   values will be 'shopping' or 'info' for now), `template_id` (string, nullable),
   `status` (string, default 'active' — values: active, suspended, pending), `plan`
   (string, nullable), timestamps.
2. Create a `Tenant` Eloquent model with `belongsTo(Client::class)`.
3. Add a nullable `tenant_id` foreign-key column to the `users` table via migration.
4. Create a `TenantContext` class (app/Services/TenantContext.php) — a simple singleton
   service (bind it in a service provider) with a `set(Tenant $tenant)`, `get(): ?Tenant`,
   and `check(): bool` method. It holds the tenant resolved for the current request. Leave
   it unset for now — Phase 2 will set it.
5. Add a factory for `Tenant` and a seeder that creates 3 sample tenants for local testing
   (2 with site_type 'shopping', 1 with 'info').
6. Run migrations and confirm they apply cleanly against the existing database without
   errors or data loss.

Do not build any UI, routes, or auth logic in this phase.
```

---

## Phase 2 — Subdomain routing & tenant resolution

```
This is the ehlom-os Laravel app. Phase 1 (already done) added a `tenants` table, a
`Tenant` model, a nullable `tenant_id` column on `users`, and a `TenantContext` singleton
service at app/Services/TenantContext.php.

GUARDRAILS:
- `portal.ehlom.com` and bare `ehlom.com` must NEVER be tenant-resolved — they are the
  agency's own CRM/admin, completely separate from client tenant sites. Existing routes
  in routes/web.php for the CRM (clients, projects, invoices, subscriptions, domains,
  agreements, leads, dashboard) must keep working exactly as before, unscoped.
- Do not modify or delete any existing routes.

TASK:
1. Create middleware `app/Http/Middleware/ResolveTenant.php`. On each request: read the
   request host. If it is exactly `portal.ehlom.com` or `ehlom.com` or `www.ehlom.com`,
   do nothing and pass through (no tenant resolution). Otherwise, extract the subdomain
   (the part before `.ehlom.com`), look it up in the `tenants` table by `subdomain`.
   - If not found: abort with a 404.
   - If found but `status = 'suspended'`: render a simple plain-text/blade view saying
     "This site is currently unavailable."
   - If found and `status = 'active'` (or 'pending'): call `TenantContext::set($tenant)`
     and continue the request.
2. Register this middleware to run globally (in the HTTP kernel / bootstrap/app.php
   depending on Laravel 13's middleware registration style) — but make sure it runs
   BEFORE any tenant-scoped route logic, and confirm it does NOT interfere with existing
   `portal.ehlom.com` CRM routes (test that clients/projects/invoices pages still load).
3. Create a new route file `routes/tenant.php` (empty for now except a placeholder
   comment) and require it conditionally from routes/web.php ONLY when a tenant is
   resolved — actual tenant routes will be added in Phase 3+.
4. Write a short note (as a code comment at the top of routes/tenant.php) on how to test
   subdomains locally, e.g. editing /etc/hosts to map something like
   testshop.ehlom-os.test to 127.0.0.1, or equivalent for whatever local dev server this
   project uses.

Verify: portal.ehlom.com CRM pages still work unchanged. A request to an unknown
subdomain returns 404. A request to a seeded tenant's subdomain (from Phase 1's seeder)
successfully resolves without error (even though there's no page to show yet — a blank
200 or a placeholder view is fine for this phase).
```

---

## Phase 3 — Tenant dashboard: auth, settings, content/pages

```
This is the ehlom-os Laravel app. Phases 1-2 (done) added a `tenants` table, `Tenant`
model, `tenant_id` column on `users`, `TenantContext` service, and `ResolveTenant`
middleware that resolves the current tenant from subdomain and skips resolution entirely
for portal.ehlom.com / ehlom.com.

GUARDRAILS:
- Tenant auth must be completely separate from the existing agency Breeze auth used at
  portal.ehlom.com. A tenant's dashboard user must never be able to log into the agency
  CRM and vice versa.
- EVERY database query in this phase must be scoped to `TenantContext::get()`. A tenant
  must never be able to see or edit another tenant's data, even by guessing IDs in a URL.
  Write at least one automated test proving this (e.g. Tenant A cannot fetch Tenant B's
  settings by ID).
- Do not touch or reuse the existing `clients` table/model.
- DESIGN: this app already has a professional, modern dashboard design system at
  `resources/views/layouts/app.blade.php` — dark navy theme, Tailwind CSS, the Figtree
  font (set in tailwind.config.js), card-based content areas, a grouped icon+label
  sidebar (section headers like "MAIN", "FINANCE", "WORK"), colored status pill badges
  (e.g. green "COMPLETED"), and a top bar with search + notifications. The tenant
  dashboard must reuse this exact same design language — same font, same color
  palette/dark theme, same sidebar pattern, same card/table styling — so a tenant's
  dashboard looks like the same professional product as the agency's own admin panel,
  not a visually distinct or lower-effort UI. Reuse/extend the existing Blade layout and
  Tailwind config rather than introducing a new design system. This applies to every
  future phase that touches tenant-facing UI (Phase 4, 5, 6), not just this one.

TASK:
1. Build login/registration views and controllers for tenant dashboard users, scoped by
   the `tenant_id` column on `users`, at routes like
   `{subdomain}.ehlom.com/dashboard/login`. Add these routes to routes/tenant.php inside
   the ResolveTenant-protected group.
2. Build a dashboard layout (sidebar) with sections: Settings, Content/Pages. Add
   placeholder disabled/hidden nav items for "Catalog" and "Payment Settings" (Phase 4 —
   only show them if the current tenant's `site_type` is 'shopping', otherwise hide).
3. Settings page: edit logo (file upload), banner image (file upload), business name,
   WhatsApp number, contact email/phone. Store on the `tenants` table (add migration
   columns as needed) or a related `tenant_settings` table — your choice, but all reads
   and writes must go through `TenantContext::get()`, never a raw ID from the request.
4. Content/Pages module: an About text field, a Gallery (many uploadable images with
   optional captions, stored in a new `tenant_gallery_images` table with `tenant_id`
   foreign key), and a Contact section (address/hours text). Build simple CRUD for the
   gallery from the dashboard.
5. Write the cross-tenant isolation test described in the guardrails above.

Verify: seeded tenants from Phase 1 can each log in independently, edit their own
settings/content, and cannot see each other's data.
```

---

## Phase 4 — Catalog + Action Button module

```
This is the ehlom-os Laravel app. Phases 1-3 (done) added tenants, subdomain routing, and
a tenant dashboard with Settings + Content/Pages, scoped via TenantContext.

GUARDRAILS:
- Do NOT reuse or modify the existing `Product` model/table. That belongs to the agency's
  own CRM (Ehlom's service packages sold to clients like domain/hosting) and is unrelated.
  Create a NEW model for tenant catalog items.
- Payment credentials belong to the TENANT, never to Ehlom. Never introduce a shared/
  platform-level Razorpay key. Every payment must use that specific tenant's own stored
  keys.
- Encrypt stored API keys/secrets using Laravel's built-in encrypted casts. Never log or
  display them in plaintext after initial entry (mask on display, e.g. show last 4 chars
  only).
- Every query scoped to TenantContext::get(), same rule as Phase 3.

TASK:
1. Create a `TenantProduct` model + migration: tenant_id, name, price, photo, category
   (nullable), description (nullable), timestamps.
2. Add an `action_type` column to `tenants` (string, default 'whatsapp' — values:
   'whatsapp' or 'razorpay').
3. Create a `PaymentSetting` model + migration: tenant_id, provider (string, e.g.
   'razorpay'), api_key (use encrypted cast), api_secret (use encrypted cast), timestamps.
4. Build a "Payment Settings" dashboard tab (only visible/reachable when the tenant's
   `action_type` is 'razorpay') where the tenant enters their own Razorpay key/secret.
5. Build the "Buy Now" / "Donate Now" button as a reusable Blade component:
   - If tenant's action_type is 'whatsapp': render a link to
     `https://wa.me/{tenant_whatsapp_number}?text={urlencoded item/site name + "I'm
     interested"}`.
   - If 'razorpay': initiate a Razorpay checkout using THAT tenant's own stored
     PaymentSetting keys (never any other key).
6. Build a "Catalog" dashboard CRUD page for managing TenantProduct records (only shown
   when tenant's site_type is 'shopping'), with photo upload.
7. If action_type is 'razorpay', add a simple read-only Orders list showing completed
   payments for that tenant only (create a minimal `tenant_orders` table populated by the
   Razorpay webhook/callback — implement a basic webhook handler that verifies the
   Razorpay signature before recording an order).

Verify: a 'whatsapp' tenant's Buy button correctly deep-links with their number and item
name. A 'razorpay' tenant's checkout uses only their own stored key (write a test or
manual check confirming tenant A's checkout never references tenant B's PaymentSetting).
```

---

## Phase 5 — Starter templates + public rendering

```
This is the ehlom-os Laravel app. Phases 1-4 (done) added tenants, subdomain routing,
tenant dashboard (Settings, Content/Pages), and Catalog + Action Button (Buy/Donate,
whatsapp or razorpay).

GUARDRAILS:
- Public template rendering must only ever show the resolved tenant's own data
  (TenantContext::get()) — never accept a tenant ID from the URL/query string for
  rendering the public page.

TASK:
1. Create `resources/views/tenant-templates/shop/` — a public storefront Blade template:
   banner image, business name, catalog grid (TenantProduct list with photo/price and the
   Buy Now button component from Phase 4), About text, Gallery, Contact section.
2. Create `resources/views/tenant-templates/info/` — banner, business name, About text,
   Gallery, a single Donate/Contact button (reusing the same button component from Phase
   4), Contact section. No catalog grid.
3. Add a `TenantHomeController` with an `index` method, routed as
   `Route::get('/', [TenantHomeController::class, 'index'])` inside the tenant-scoped
   route group (routes/tenant.php). It reads `TenantContext::get()`, checks
   `template_id`: if it's 'shop' or 'info', render the matching template with that
   tenant's Content/Pages + Catalog data; if `template_id` is null or doesn't match
   either, default to 'info'.
4. Add a `template_id` dropdown selector to the dashboard Settings page (Phase 3),
   restricted to 'shop' or 'info' only.

Verify: visiting a seeded 'shopping' tenant's subdomain root URL renders the shop template
with their own catalog items and correct Buy button behavior; visiting a seeded 'info'
tenant renders the info template with their own content, no catalog grid, correct
Donate button behavior.
```

---

## Phase 6 — Super-admin tenant view

```
This is the ehlom-os Laravel app. Phases 1-5 (done) built out the full tenant-facing
system: tenants table, subdomain routing, tenant dashboard, catalog/payments, public
templates.

GUARDRAILS:
- This phase only touches the EXISTING agency admin panel at portal.ehlom.com (unscoped,
  behind the existing Breeze auth). Do not add tenant-context scoping here — this view is
  intentionally cross-tenant, for the agency owner only.

TASK:
1. Add a new "Tenants" section to the existing portal.ehlom.com admin nav/routes
   (routes/web.php, inside the existing `auth` middleware group — NOT routes/tenant.php).
2. Build an index page listing all tenants: subdomain, site_type, template_id, plan,
   status, and — if `client_id` is set — a link to that Client's existing CRM page
   (route already exists: clients.show or similar, check existing ClientController).
3. Add a "suspend" / "activate" action (toggles the tenant's `status` column between
   'active' and 'suspended' — the Phase 2 ResolveTenant middleware already enforces this
   on the public side).
4. Add a "create tenant" form for manually onboarding a client being migrated: fields for
   subdomain, name, site_type, template_id, plan, and an optional dropdown to link an
   existing Client record.

Verify: the existing CRM pages (clients, projects, invoices, etc.) are unaffected. The new
Tenants list correctly shows all tenants including the ones seeded in Phase 1.
```

---

## Phase 7 — Migrate existing real clients (manual, do per client)

Not a single AI prompt — do this per client using the Phase 6 admin panel, after Phases
1-6 are live and verified on the VPS (Phase 0):

1. **Shopping clients (Razorpay or WhatsApp):** create their tenant (site_type=shopping,
   template_id=shop, correct action_type), re-enter their catalog via the dashboard.
2. **Portfolio/church/village clients (static HTML on Bunny currently):** create their
   tenant (site_type=info, template_id=info), recreate their About/Gallery/Contact
   content via the dashboard. If their existing design doesn't fit the `info` template,
   treat that one as a separate paid custom-template job — don't let it block migrating
   the rest.
3. Once a client's new subdomain is verified working end-to-end, retire their old Bunny
   hosting for that site.

Do NOT let any AI model do bulk/automatic migration of real client data without a human
reviewing each one — these are live paying clients' actual sites.
