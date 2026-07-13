# Standalone phase prompts

Each prompt below is self-contained — paste ONE at a time into a fresh AI coding session
(DeepSeek, Nemotron, etc). Do not paste more than one phase per session. Wait for a human
(or a stronger model) to review the diff before starting the next phase — the guardrails
repeated in every prompt are there because a weaker/free model may otherwise take shortcuts
that break tenant data isolation or leak payment credentials.

Do these in order: 0 → 1 → 2 → 3 → 4 → 5 → 6 → 7 → 9 → 10 → 11 → 12 → 13 → 14.
Do not skip ahead. (Phase 8 was the informal "later" bucket in the original build plan;
phases 9-14 are that bucket, now broken into concrete, individually-reviewable steps.)

Phases 1-7 (foundation through client migration) are done and live in production as of
this writing. Phases 9-14 add: a WordPress-style theme gallery + customizer, a reusable
module system so the same dashboard works across industries without rebuilding it, a
minimal cart/COD/Prepaid/shipping e-commerce core, order status + customer order lookup,
and an agency-billed add-on marketplace. Phase 11 in particular is the highest-risk of
this whole set — it refactors how existing tenant access control works, not just adding
new features — review it especially carefully and insist on the backward-compatibility
verification it calls for before deploying.

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

DESIGN REFINEMENT (apply this everywhere in this phase, AND retrofit it onto the
existing tenant dashboard views from Phase 3-4 and the agency admin layout — all three
surfaces must match):
- Replace DM Mono as the general UI/body font. Monospace fonts read as "developer tool,"
  not "small business dashboard" — the actual users here are shop owners, church admins,
  school staff, not developers. Switch body text, labels, nav items, and form fields to
  a humanist sans-serif (Inter or Manrope, loaded the same way as the existing Google
  Fonts link in the layout head). Keep Syne for the big brand headline/logo text only —
  its distinctive character works there.
- Tone down or remove the starfield/constellation decorative background on auth pages
  (login/register). It reads as a generic tech-startup template rather than a trustworthy
  business tool for this audience. Replace with a subtle solid dark gradient or remove
  the decorative background entirely, keeping the rest of the layout (card, logo, form)
  unchanged.
- Do not change the color palette, sidebar structure, or overall dark theme — those are
  fine. This is a targeted typography + background fix, not a redesign.
- Apply consistently: `resources/views/layouts/app.blade.php` (agency admin),
  `resources/views/tenant/layouts/dashboard.blade.php` and
  `resources/views/tenant/auth/*.blade.php` (tenant dashboard + auth), and the new public
  storefront templates being built in this phase.

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
5. Apply the design refinement above to the new templates AND go back and update the
   existing admin/tenant layout files listed above so all three surfaces are visually
   consistent.

Verify: visiting a seeded 'shopping' tenant's subdomain root URL renders the shop template
with their own catalog items and correct Buy button behavior; visiting a seeded 'info'
tenant renders the info template with their own content, no catalog grid, correct
Donate button behavior. Confirm the font/background changes appear on the public
templates, the tenant dashboard, and the agency admin panel — all three, not just one.
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

---

## Phase 9 — Theme registry + template gallery

```
This is the ehlom-os Laravel app. Templates currently exist as a hardcoded 2-option
choice ('shop', 'info') validated via Rule::in() in both AdminTenantController and
TenantSettingsController, rendered from resources/views/tenant-templates/{key}/index.blade.php.
This phase turns that into a proper registry so templates can be browsed as a gallery
(like WordPress themes) and new ones added without touching validation code every time.

GUARDRAILS:
- Do not change how existing 'shop'/'info' templates render. They become registry
  entries, not a rewrite.
- Do not remove the ability for a template to be "private" (assignable only by the
  agency admin, not selectable by the tenant themselves) - this is needed for one-off
  custom templates built for a specific paying client.
- Every tenant-facing query/render must remain scoped through TenantContext as
  established in earlier phases - this phase only touches template SELECTION, not
  tenant data isolation.

TASK:
1. Create config/themes.php returning an array keyed by template_id, each entry:
   ['name' => 'Shop Classic', 'description' => '...', 'thumbnail' => 'path/to/preview.png'
   (a placeholder image is fine for now), 'industries' => ['shopping'], 'public' => true].
   Add entries for the existing 'shop' and 'info' templates with public => true.
2. Replace the hardcoded Rule::in(['shop','info']) validation in AdminTenantController
   and TenantSettingsController with a check against array_keys(config('themes')).
3. Build a reusable Blade component (resources/views/components/theme-gallery.blade.php)
   that renders a grid of theme cards (thumbnail, name, description, a "Select" radio/
   button) instead of a plain <select> dropdown. Use it in both:
   - resources/views/tenants/form.blade.php (admin creating/editing a tenant) - show ALL
     registry entries here, public and private.
   - resources/views/tenant/settings/index.blade.php (tenant's own template picker) -
     show only entries where public === true.
4. Confirm existing tenants with template_id='shop' or 'info' keep rendering exactly as
   before - no visual regression.

Verify: the admin tenant form and the tenant's own Settings page both show a visual
gallery instead of a dropdown, correctly filtered by public/private, and selecting a
template still updates template_id and renders correctly on the public storefront.
```

---

## Phase 10 — Theme customizer (per-tenant color/layout settings)

```
This is the ehlom-os Laravel app. Phase 9 (done) added a theme registry and gallery
picker. Templates themselves are still fully static - this phase lets a tenant
customize color/layout within their chosen template, without touching code, similar to
the WordPress Customizer.

GUARDRAILS:
- Must not break rendering for any tenant with no customization saved yet (null/empty
  theme_settings) - always fall back to sane hardcoded defaults matching current look.
- Keep the customizable surface small and safe: an accent color, and 2-3 layout toggles
  (e.g. show/hide gallery section, hero image style). Do NOT allow arbitrary raw CSS/HTML
  injection from the tenant - that's an XSS risk on their own public storefront. If you
  add a "custom CSS" field at all, it must be sanitized/escaped, never rendered as raw
  <style> content pulled directly from user input without validation.

TASK:
1. Add a migration: nullable `theme_settings` JSON column on tenants.
2. Add a "Customize" tab to the tenant dashboard (new controller/route, e.g.
   TenantThemeController) with a simple form: accent color (a constrained set of
   preset color options, not a free-text hex field, to keep this safe and on-brand),
   and toggles for section visibility (e.g. hide gallery, hide about).
3. Update resources/views/tenant-templates/shop/index.blade.php and info/index.blade.php
   to read from $tenant->theme_settings (with defaults via ?? or array_merge with a
   defaults array) and apply the accent color via a CSS custom property
   (e.g. style="--tp-accent: {{ $accentColor }}") instead of hardcoded color values,
   and conditionally render sections based on the visibility toggles.

Verify: an tenant with no theme_settings saved renders identically to before this phase.
Setting a different accent color visibly changes their storefront's accent color.
Toggling a section off hides it on the public page.
```

---

## Phase 11 — Generalize the module system (reusable dashboard across industries)

```
This is the ehlom-os Laravel app. Right now the tenant dashboard shows/hides
Catalog/Payments/Orders based on hardcoded checks against `site_type === 'shopping'`
and `action_type === 'razorpay'` (see requireShoppingSite() in TenantCatalogController,
similar checks in TenantOrderController/TenantPaymentSettingsController, and the nav
logic in resources/views/tenant/layouts/dashboard.blade.php). This phase generalizes
that into a proper module system so future industries (restaurant, church, school) can
reuse the same dashboard shell instead of hardcoding new site_type branches forever.

GUARDRAILS - this is the highest-risk phase so far, be careful:
- Existing 'shopping' tenants (with action_type whatsapp or razorpay) and 'info' tenants
  MUST continue working EXACTLY as before after this change - this is a refactor of how
  access is determined, not a change to what any existing tenant can currently do.
- Write a migration/backfill step that maps every EXISTING tenant's current
  site_type/action_type into the new module representation automatically - do not leave
  existing tenants in a broken state with no modules enabled.
- Every module-gated controller action must still enforce access server-side (not just
  hide nav items) - this was a real bug found and fixed in an earlier phase (Phase 4's
  site_type gating was originally UI-only), do not reintroduce that class of bug here.

TASK:
1. Add a migration: nullable `modules` JSON column on tenants (an array of enabled
   module keys, e.g. ["content","catalog","payments","orders"]).
2. Create config/modules.php: a registry of module key => [label, icon, nav section,
   description]. Include at minimum: content, catalog, payments, orders (matching what
   exists today).
3. Write a one-time migration/console command that backfills the `modules` column for
   every existing tenant based on their current site_type/action_type (e.g. site_type
   shopping => ['content','catalog'] plus 'payments','orders' if action_type=razorpay;
   site_type info => ['content']).
4. Replace the hardcoded site_type/action_type checks in TenantCatalogController,
   TenantOrderController, TenantPaymentSettingsController with a check against
   in_array($moduleKey, $tenant->modules ?? []) - add a small helper (e.g. a method on
   the Tenant model: hasModule(string $key): bool) to keep this DRY.
5. Update the dashboard sidebar (resources/views/tenant/layouts/dashboard.blade.php) to
   loop over $tenant->modules against config('modules') instead of the current hardcoded
   if-checks.
6. Add a "Modules" section to the AdminTenantController create/edit form so the agency
   can hand-toggle individual modules per tenant, not just rely on a site_type preset.

Verify: run the backfill against a copy of production-like seeded data (the existing
testshop1/testshop2/testinfo1 seeders) and confirm every existing tenant's dashboard
looks and behaves IDENTICALLY to before this phase - same nav items, same access. Then
confirm the site_type gating bypass test from the earlier security review still fails
correctly (an info-type tenant still cannot reach dashboard/catalog directly by URL).
```

---

## Phase 12 — Cart, COD/Prepaid checkout, and shipping capture

```
This is the ehlom-os Laravel app. Today's "Buy Now" flow (Phase 4, fixed in a later
session) is single-product, instant-checkout only - no cart, no shipping address
capture, and Cash on Delivery doesn't exist as an option at all (only WhatsApp deep-link
or immediate Razorpay payment). This phase adds a real (but intentionally minimal) cart
+ checkout: multiple items, choice of COD or Prepaid, and a shipping address captured at
checkout. Per explicit product decision, this does NOT include full customer accounts/
login for v1 - use guest checkout with order lookup by phone number (Phase 13 territory)
rather than building customer auth here.

GUARDRAILS:
- Do NOT remove or break the existing single-product instant "Buy Now" Razorpay flow
  (tenant-action-button.blade.php, TenantWebhookController::handleRazorpay) - it is
  tested and working. This phase adds cart+checkout as the PRIMARY flow going forward,
  but the existing quick-buy button can remain for single-item impulse purchases if it
  doesn't conflict; do not silently deprecate working code without flagging it in your
  summary.
- Cart contents belong to a browser session (guest), not a database-backed customer
  account - keep this simple, no new auth system in this phase.
- Every cart/checkout query must remain scoped to the CURRENT tenant (via TenantContext)
  - a shopper's cart on one tenant's subdomain must never leak into or affect another
  tenant's data, consistent with every isolation guarantee established in earlier phases.
- Never trust client-submitted price data for the final charge amount - always
  recalculate order totals server-side from the actual TenantProduct.price values at
  checkout time, not from anything posted by the browser.

TASK:
1. Add a migration: create tenant_order_items table (order_id FK to tenant_orders,
   tenant_product_id FK, quantity, unit_price) - this lets one order contain multiple
   products, unlike today's single tenant_product_id column on tenant_orders. Keep the
   existing tenant_product_id column on tenant_orders for backward compatibility with
   the existing single-item Razorpay webhook flow, but new cart-based orders should use
   the new order_items table.
2. Add a migration: shipping_name, shipping_phone, shipping_address, shipping_pincode,
   payment_method (enum: cod, prepaid) columns on tenant_orders.
3. Build a session-based cart: "Add to Cart" button on TenantProduct cards (shop
   template), a /cart page showing items with quantity adjust/remove, cart count in the
   storefront header.
4. Build a checkout page: shipping address form + payment method choice (COD always
   available; Prepaid/Razorpay only shown if the tenant has action_type=razorpay and a
   configured PaymentSetting).
   - COD: create the TenantOrder + order_items immediately with status='pending', no
     payment processing needed.
   - Prepaid: create the order as 'awaiting_payment', then trigger Razorpay checkout for
     the CART TOTAL (recalculated server-side); on successful webhook, update status to
     'paid' (reuse/extend the existing webhook handler, now needs to handle a cart-based
     order rather than assuming a single product).
5. Show an order confirmation page/message after checkout with an order number the
   customer can reference later (Phase 13 will add lookup-by-phone).

Verify: add multiple products to cart, complete a COD checkout - confirm a TenantOrder
with correct order_items and shipping details is created with status=pending, no payment
attempted. Then complete a Prepaid checkout with a test Razorpay key (same approach as
the earlier verified test) - confirm the checkout modal opens with the correct CART
TOTAL, not a single product's price. Confirm the existing single-product instant Buy Now
button (if kept) still works unchanged.
```

---

## Phase 13 — Order status workflow + customer order lookup

```
This is the ehlom-os Laravel app. Phase 12 (done) added cart-based checkout with COD/
Prepaid and shipping capture. TenantOrderController is currently read-only. This phase
lets the shop owner update fulfillment status, and lets their customer check an order's
status without needing a full account (per the earlier decision to skip customer login
for v1).

GUARDRAILS:
- Status updates must remain scoped to the tenant's OWN orders only (TenantContext, same
  pattern as every other tenant controller).
- The public order-lookup page must NOT allow browsing/enumerating other customers'
  orders - require BOTH the order number AND the phone number used at checkout to match
  before showing any order detail (prevents a stranger from guessing order numbers
  sequentially to see other people's addresses/order contents).

TASK:
1. Add a `status` enum column (if not already covered by Phase 12's migrations) with
   values: pending, confirmed, shipped, delivered, cancelled. Default 'pending'.
2. Make TenantOrderController's index page allow the tenant to update an order's status
   via a dropdown/button per row (scoped to their own tenant_id, as established).
3. Add a public route (e.g. GET/POST {subdomain}.ehlom.com/track) with a simple form:
   order number + phone number. On submit, look up the order WHERE order number matches
   AND shipping_phone matches (both required) - show status, items, and shipping address
   only on a match. No match = generic "not found," don't leak which field was wrong.
4. Link to this tracking page from the order confirmation shown at the end of Phase 12's
   checkout flow.

Verify: as the tenant, change an order's status and confirm it persists and is scoped
correctly (cannot be changed by a different tenant's dashboard). As a guest, confirm the
tracking page correctly requires both order number and phone to match, and rejects a
correct order number with a wrong phone number.
```

---

## Phase 14 — Add-on marketplace (WhatsApp automation, AI agent, etc.)

```
This is the ehlom-os Laravel app. This phase adds a self-serve add-on marketplace in the
tenant dashboard, separate from the tenant's own customer-facing COD/Prepaid orders -
these are add-ons the TENANT pays THE AGENCY for (billing you), not anything their
shoppers interact with.

GUARDRAILS:
- This is agency billing, not tenant storefront payments - do not reuse PaymentSetting/
  the tenant's own Razorpay keys for this. If actual charging is automated in this phase,
  it must go through the agency's OWN payment collection, separate from any tenant's
  configured gateway.
- Given the complexity of full billing automation, it is acceptable (and preferred for
  this phase) to only build the toggle/tracking mechanism and flag it for the agency to
  invoice manually via the existing Invoice/Subscription models already in the CRM -
  don't build a second, parallel billing/charging system in this phase. State clearly in
  your summary if you scoped it this way.

TASK:
1. Create config/addons.php: registry of addon key => [name, price, description].
2. Add a migration: tenant_addons table (tenant_id, addon_key, status [active/inactive],
   activated_at).
3. Add a "Marketplace" tab in the tenant dashboard listing available add-ons with a
   toggle. Toggling on creates/updates a tenant_addons row; toggling off marks it
   inactive (don't hard-delete, keep history).
4. Surface active add-ons per tenant on the agency's own /tenants admin page (Phase 6),
   so you can see who has what enabled and invoice accordingly using the existing
   Invoice model.

Verify: toggling an add-on on/off correctly updates tenant_addons scoped to the current
tenant. The agency admin tenants list shows each tenant's active add-ons.
```
