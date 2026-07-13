# SaaS product requirements vs. current state vs. what's being built next

Consolidated from every requirement discussed. For each item: what was asked for,
what actually exists in the code right now, and what's missing.

---

## 1. Template system

**Asked for:** admin manages templates entirely from the UI, no code editing. Industry-
specific templates (shopping, restaurant, church, school, portfolio). A way to turn a
real custom-built client site into a reusable template (the Duda/GoHighLevel "Snapshot"
pattern — clone an existing account, not write a new code file).

**Exists:** `config/themes.php` (a code file, not admin-editable) with 2 templates:
`shop`, `info`. Admin picks from a gallery (Phase 9) and customizes colors/sections
(Phase 10) — but the *list of available templates* itself requires editing PHP.

**Gap / building now:**
- Move templates from a config file into a database table (`themes`), with a real admin
  CRUD screen: create/rename/deactivate, tag which industries a template suits
- **"Save as Template"**: from any existing tenant, admin clicks one button to snapshot
  its current template + content structure + theme_settings into a new, reusable
  library entry — no code touched. This is the primary mechanism for custom designs
  going forward, not raw Blade file authoring.
- Restaurant/school/church templates: at least one real example beyond shop/info,
  proving the pattern works for a second industry, not just the original two

## 2. Reusable dashboard per industry (not identical dashboards)

**Asked for:** confirmed NOT the same screens for shopping vs. portfolio — different nav
items, different pages, per industry.

**Exists:** the module system (Phase 11) already does this correctly — a portfolio
tenant's sidebar shows only Dashboard/Content/Settings; a shopping tenant additionally
sees Catalog/Payments/Orders. Verified working.

**Gap:** only 2 industries currently have real module presets (shopping, info).
Restaurant/school/church need their own module combinations defined (e.g. restaurant =
content + catalog[relabeled menu] + orders, no payments module if WhatsApp-only).

## 3. Add-on marketplace with real payment gating

**Asked for:** client sees an add-on, must pay before it activates — they cannot turn it
on for free. Admin's own billing is NOT locked to Razorpay — stays offline/manual now,
flexible for whatever payment method gets integrated later (cash, bank transfer, etc.).

**Exists:** `TenantAddonController::toggle()` — a **free toggle**, no payment step at
all. This is a real, confirmed bug relative to what was asked for.

**Gap / building now:**
- Toggle becomes locked-until-paid. Since admin billing is explicitly NOT tied to
  Razorpay, the "payment" step for v1 is **admin-confirmed manual activation** — client
  requests/clicks "I want this," it shows as "Pending — awaiting confirmation," and only
  an admin action (after receiving payment however you choose to collect it) flips it
  active. This matches "offline payment" exactly as instructed, and leaves room to wire
  in a real online payment step later without redesigning the flow.
- Every add-on's status change must be server-side / admin-triggered only — a client can
  never flip their own add-on to "active."

## 4. Admin billing/financial visibility

**Asked for:** admin sees everything — active add-ons, when activated, by whom, full
control including manual override.

**Exists:** admin sees which add-ons are active (Phase 14), nothing else — no request
history, no manual activation control, no connection to the existing `Invoice` model.

**Gap / building now:** a real admin screen per tenant showing every add-on request
(pending/active/inactive), who requested it and when, with **Activate**/**Deactivate**
buttons — the actual manual-confirmation control point from item 3.

## 5. Client (CRM) ↔ Tenant (SaaS) duplication

**Asked for:** stop having two disconnected records for the same business.

**Exists:** `Tenant.client_id` is optional and decorative — nothing syncs between them.

**Gap / building now:** when an add-on gets manually activated for a tenant that has a
linked `client_id`, record it as a line item against that client using the **existing**
`Invoice`/`InvoiceItem` models already powering your renewal-invoice automation — so a
tenant's add-on charges show up in the same financial records as everything else for
that client, not a separate parallel ledger.

## 6. Custom / one-off client websites, no CloudPanel access for clients

**Asked for:** clarified and confirmed, no new build needed — client never touches
CloudPanel, ever. Two paths: fits the template system → full dashboard/SaaS treatment
(covered by item 1's Save-as-Template flow); doesn't fit at all → traditional hosting
outside the Tenant system, deployed by the agency only, no dashboard. This is a
documented boundary, not a code gap.

## 7. Self-serve signup + pay-before-access (mentioned earlier, still parked)

**Asked for:** a client can sign up, pick a plan, and only gets dashboard access after
paying.

**Status:** intentionally still parked — this needs the billing/gating pattern from
items 3-4 proven on add-ons first (smaller, reversible surface) before applying the same
pattern to the much higher-stakes "does this person get a tenant at all" decision. Not
starting this today.

---

## Build order for today

1. Database-backed `themes` table + admin CRUD (replaces the config file)
2. "Save as Template" — duplicate an existing tenant into a new library entry
3. Add-on marketplace: locked-until-admin-confirms flow, replacing the free toggle
4. Admin screen: per-tenant add-on requests with Activate/Deactivate control
5. Wire add-on activation into the existing `Invoice` model for tenants with a linked
   `client_id`

Each step gets built, tested locally, then deployed and verified on production the same
way as every phase before this — no step skipped.
