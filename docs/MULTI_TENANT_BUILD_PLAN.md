# Ehlom OS → Multi-Tenant Platform: Build Guide

This document is written so any AI coding agent (Claude Code or otherwise) can pick it up
cold and continue the work. Read this whole file before touching code.

## Context for the agent

`ehlom-os` is currently a **single-tenant internal CRM** for a web agency (Ehlom Digital).
It has: `Client`, `Project`, `Invoice`, `Subscription`, `Domain`, `Agreement`, `Lead` models,
single admin auth (Laravel Breeze), no tenant concept, no public storefronts.

Goal: turn this into a **multi-tenant SaaS platform** where each client gets their own
subdomain (`clientname.ehlom.com`) with a login dashboard to manage their own site content,
while the existing CRM (Client/Invoice/Subscription/Project) keeps working for Ehlom's own
agency billing of those clients. **Do not delete or repurpose the `clients` table** — it is
Ehlom's own CRM data and existing code depends on it. Tenancy is additive.

## Architecture decisions (already made — do not re-litigate these)

- **Single database, single app instance, single-database multi-tenancy.** No
  `stancl/tenancy` package, no per-tenant database. A `tenant_id` foreign key +
  Eloquent global scope is enough at this scale and is far simpler to operate.
- **Subdomain-based routing** (`clientname.ehlom.com`), not path-based
  (`ehlom.com/clientname`). One wildcard DNS record, one wildcard SSL cert, one
  CloudPanel vhost for the whole app. Custom domains (later phase) CNAME onto the
  subdomain.
- **A `Tenant` model is separate from `Client`.** A `Client` (agency CRM record) may
  *optionally* own one `Tenant` (their live site). Existing Client/Project/Invoice/
  Subscription relations are untouched.
- **Feature modules, not "industries."** Instead of building a separate feature set per
  vertical (shop / church / portfolio / restaurant / school), build 3 reusable modules
  that any tenant can turn on:
  1. **Content/Pages** — banner, about, gallery, contact (every tenant has this)
  2. **Catalog** — list of items (products, menu items, event listings)
  3. **Action Button** — attached to catalog items or standalone; per-tenant configurable
     as either `whatsapp` (deep-link to `wa.me/{number}?text=...`) or `razorpay`
     (real checkout using the tenant's own Razorpay keys — never Ehlom's).
  This covers: shopping (Razorpay), shopping (WhatsApp), portfolio, church (donate button
  = same Action Button, relabeled), village sites, and later restaurant menus and school
  inquiry forms — without new architecture per vertical.
- **Payments are never routed through Ehlom's own merchant account.** Each tenant enters
  their own Razorpay (or later Stripe/PayPal) keys in their dashboard. Ehlom's revenue is
  purely the subscription fee, kept legally/financially separate from client transactions.
- **Khulkon TV (streaming platform) is explicitly out of scope** — it stays on its own
  dedicated VPS as a standalone custom build. Never fold it into this system.
- **One shared design system for admin and tenant dashboards.** The existing agency
  admin panel (`resources/views/layouts/app.blade.php`) already has a professional,
  modern look: Tailwind CSS, the Figtree font, a dark navy theme, card-based content
  areas, a grouped icon+label sidebar, colored status pill badges. The tenant dashboard
  (Phase 3+) must reuse this same layout/design system, not invent a separate one — a
  client's dashboard should look like the same product as the agency's own, not a
  cheaper-looking clone.

## Phase order

Do not skip ahead. Test each phase against a real or seeded tenant before starting the next.

---

### Phase 0 — Infrastructure move (mostly manual, not a Claude Code prompt)

Goal: get off the Bunny Docker container and onto the VPS before building tenancy on it.

Manual steps (do these yourself, in this order):
1. Provision the app on the VPS under CloudPanel (PHP 8.3, MySQL, one site).
2. Point `portal.ehlom.com` DNS at the VPS.
3. Add a **wildcard** DNS record: `*.ehlom.com` → VPS IP (A record).
4. In CloudPanel, configure the vhost's domain as `ehlom.com` with `*.ehlom.com` as an
   alias (or a wildcard server_name in the underlying Nginx vhost if CloudPanel's UI
   doesn't expose wildcard aliases directly — check CloudPanel docs for "wildcard
   domain").
5. Issue a wildcard Let's Encrypt certificate (`*.ehlom.com`) via DNS-01 challenge (needs
   API access to your DNS provider — CloudPanel supports this for supported providers).
6. Deploy the current `ehlom-os` app to this vhost, confirm `portal.ehlom.com` works
   exactly as it does today before proceeding to Phase 1.

---

### Phase 1 — Tenant foundation (additive)

> Paste to Claude Code:
>
> "In this Laravel app (`ehlom-os`), add multi-tenancy WITHOUT modifying the existing
> `clients` table or any of its relations. Create a new `tenants` table with: `client_id`
> (nullable foreign key to `clients`, since not every tenant necessarily maps 1:1 to a CRM
> client record), `subdomain` (unique, string), `name`, `site_type` (enum or string:
> `shopping`, `info`, — start with just these two, more added later), `template_id`
> (string, references a template key, not a DB-driven template system yet), `status`
> (enum: `active`, `suspended`, `pending`), `plan` (string), timestamps. Create a `Tenant`
> model with a `belongsTo(Client::class)` relation (nullable) and a `hasMany` for tenant
> users (create a `tenant_id` nullable column on the `users` table, and a global scope on
> any tenant-owned model that filters by the currently resolved tenant — build a
> `TenantContext` singleton service that holds the currently resolved tenant for the
> request, set by middleware in Phase 2). Add a migration and a factory/seeder that creates
> 2-3 sample tenants for local testing. Do not touch routes or public site rendering in
> this phase — this is data-layer only."

---

### Phase 2 — Subdomain routing & tenant resolution

> Paste to Claude Code:
>
> "Add middleware `ResolveTenant` that runs on every request: extract the subdomain from
> the request host (everything before `.ehlom.com`; skip resolution entirely if the host
> is `portal.ehlom.com` or `ehlom.com` itself, since those remain the agency CRM/admin —
> do not tenant-scope those). Look up the `tenants` table by `subdomain`; if found and
> `status = active`, bind it into the `TenantContext` singleton for the rest of the
> request; if not found, return a 404; if `status = suspended`, show a simple 'this site is
> currently unavailable' page. Register this middleware on a new route group in
> `routes/web.php` (or a new `routes/tenant.php` required conditionally) that will hold all
> public storefront and tenant-dashboard routes going forward, separate from the existing
> agency CRM routes which stay unscoped. Write a local testing note in the PR/commit
> message on how to test subdomains locally (e.g. editing `/etc/hosts` or using
> `*.ehlom-os.test` with Laravel Valet/Herd)."

---

### Phase 3 — Tenant dashboard: auth, settings, content/pages module

> Paste to Claude Code:
>
> "Build tenant-scoped authentication: a tenant has its own users (via the `tenant_id`
> column added in Phase 1), separate login at `{subdomain}.ehlom.com/dashboard/login`,
> completely isolated from the agency's own Breeze auth at `portal.ehlom.com`. After
> login, build a dashboard shell with a sidebar showing: Settings, Content/Pages (Phase 3
> scope), and placeholders for Catalog / Payment Settings (Phase 4, hidden if
> `site_type != shopping`). Build the Settings page: logo upload, banner image, business
> name, WhatsApp number, contact info — scoped to `TenantContext::current()`, never
> editable across tenants. Build the Content/Pages module: About text, a Gallery
> (many uploadable images with captions), Contact section — all stored against the
> tenant, editable from the dashboard. Ensure every query in this phase is scoped to the
> resolved tenant — write a test that confirms Tenant A's dashboard cannot see or edit
> Tenant B's data even if IDs are guessed in the URL."

---

### Phase 4 — Catalog + Action Button module

> Paste to Claude Code:
>
> "Add a `TenantProduct` model (items belonging to a tenant's catalog: name, price, photo,
> category, description) — do not reuse or modify the existing `Product` model, which is
> Ehlom's own service-package catalog for agency billing and is unrelated. Add a
> `action_type` setting on the `tenants` table: `whatsapp` or `razorpay`. Add a
> `PaymentSetting` model/table (tenant_id, provider, api_key_encrypted, api_secret_encrypted
> — use Laravel's encrypted casts, never store these in plaintext) editable from a new
> 'Payment Settings' dashboard tab, visible only when `action_type = razorpay`. Build the
> 'Buy Now' / 'Donate Now' button component used on the public storefront: if the tenant's
> `action_type` is `whatsapp`, it links to `https://wa.me/{tenant_whatsapp}?text={prefilled
> item message}`; if `razorpay`, it initiates a real Razorpay checkout using that specific
> tenant's own stored keys (never a shared/Ehlom key). Add a dashboard 'Catalog' CRUD page
> for managing `TenantProduct` records with photo upload. If `action_type = razorpay`, also
> add a simple Orders list showing completed Razorpay payments for that tenant only."

---

### Phase 5 — Starter templates + public rendering

> Paste to Claude Code:
>
> "Create two public storefront Blade template sets under
> `resources/views/tenant-templates/`: `shop` (banner, catalog grid, Buy Now buttons,
> about/contact) and `info` (banner, about, gallery, Donate/Contact button — no catalog
> grid). Add a route `Route::get('/', TenantHomeController::class)` inside the
> tenant-scoped route group (from Phase 2) that resolves the current tenant, checks its
> `template_id` field, and renders the matching template with that tenant's Content/Pages
> and Catalog data. If a tenant's `template_id` doesn't match a known template, fall back
> to `info`. Add a `template_id` selector to the tenant dashboard Settings page, restricted
> to the currently available template keys (`shop`, `info`)."

---

### Phase 6 — Super-admin tenant view

> Paste to Claude Code:
>
> "On the existing agency admin panel (`portal.ehlom.com/admin`, unscoped), add a Tenants
> section: list all tenants with subdomain, site_type, template, plan, status, and (if
> `client_id` is set) a link to the linked CRM Client record. Add actions to
> suspend/activate a tenant (toggles `status`, which the Phase 2 middleware already
> enforces) and to manually create a new tenant (subdomain, site_type, template, linked
> client) for onboarding existing clients being migrated in Phase 7."

---

### Phase 7 — Migrate existing clients (manual + scripted, not a single prompt)

Do per-client, using the super-admin panel from Phase 6:

1. **Shopping clients (Razorpay or WhatsApp):** create their tenant record, set
   `site_type = shopping`, `template_id = shop`, `action_type` to match what they already
   use, re-enter their catalog items via the dashboard (or write a one-off import script
   per client if their existing product list is large).
2. **Portfolio / church / village clients (currently static HTML on Bunny):** create
   their tenant record, `site_type = info`, `template_id = info`, recreate their About/
   Gallery/Contact content via the Content/Pages dashboard. If a client's existing design
   doesn't fit the `info` template, treat it as a paid custom-template job, not a blocker
   for migrating the rest.
3. Once a client's new subdomain is verified working, repoint or retire their old Bunny
   hosting for that site.

---

### Phases 9-14 — Reusable SaaS platform layer (full prompts in PHASE_PROMPTS_STANDALONE.md)

What was Phase 8's informal "later" bucket is now broken into concrete, individually
reviewable phases:

- **Phase 9 — Theme registry + gallery.** Templates become a WordPress-style browsable
  library (thumbnail, name, public/private) instead of a hardcoded 2-option dropdown.
  Private entries support one-off custom templates for a specific paying client.
- **Phase 10 — Theme customizer.** Per-tenant color/layout settings (accent color,
  section visibility) applied to their chosen template without touching code.
- **Phase 11 — Generalized module system.** Replaces the hardcoded
  `site_type === 'shopping'` gating with a proper enabled-modules list per tenant, so the
  same dashboard shell is reusable across future industries (restaurant's menu is just
  Catalog relabeled; school's admissions form is a new module) without rebuilding it each
  time. Highest-risk phase in this set — it refactors existing access control, requires a
  backfill for every already-live tenant, and needs full regression testing against the
  isolation guarantees established in earlier phases.
- **Phase 12 — Cart + COD/Prepaid + shipping.** A real (intentionally minimal) e-commerce
  core: multi-item cart, Cash on Delivery as a first-class option alongside Razorpay,
  shipping address capture. Explicitly skips full customer accounts/login for v1 — guest
  checkout with phone-number order lookup instead, to keep scope down per product
  decision.
- **Phase 13 — Order status + customer lookup.** Shop owner can update fulfillment status
  (pending → confirmed → shipped → delivered); customer can check status via order
  number + phone, no login required.
- **Phase 14 — Add-on marketplace.** Tenant self-toggles paid add-ons (WhatsApp
  automation, AI agent, etc.) in their dashboard; billed through the agency's own
  existing `Subscription`/`Invoice` models, kept entirely separate from any tenant's own
  customer-facing Razorpay/COD payments.

### Still later, not yet scoped into concrete phases

- Self-serve signup (public form → payment → auto-creates tenant + owner login) — needs
  Phase 12-14's billing patterns proven first
- Custom domain support (CNAME mapping, verification, SSL automation — likely needs
  Caddy or Cloudflare for SaaS rather than manual Let's Encrypt per domain)
- AI lead-research agent (auto-drafts a tenant from a lead's quote-form answers)
- AI dashboard assistant (edits a tenant's existing template fields/content via chat —
  explicitly NOT free-form code generation; keep it constrained to the modules already
  built, to avoid breaking the single-shared-codebase model)
