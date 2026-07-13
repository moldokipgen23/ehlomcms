# Path to a fully complete SaaS platform

Everything remaining, honestly categorized. Verified against the actual code/server
where noted — not guessed. Last reconciled 2026-07-14, against git log up to `278a8c7`.

---

## Architecture (settled, do not re-litigate)

Considered and rejected: Docker-per-tenant + database-per-tenant isolation. Rejected
because the business shape here is many small SMB clients (shops, restaurants,
schools), not a few large enterprise tenants — per-container isolation is how you'd
build for the latter, not the former (Shopify/Wix/GoHighLevel all run pooled
multi-tenant, not container-per-customer, at this shape). Kept: one shared codebase,
one shared database, `tenant_id`-scoped rows, per-tenant storage folders
(`storage/tenants/{id}/...`). Real cross-tenant safety comes from enforced query
scoping + per-tenant rate/storage guardrails, not from infrastructure walls. See
`MULTI_TENANT_BUILD_PLAN.md` for the original architecture decision record.

---

## ✅ Resolved since the last pass — verified via git log, not assumed

- **Automated database backups** — done (`6b6d268`): daily gzip dump, 14-day
  retention, via the Laravel scheduler.
- **Tenant password reset** — done (`2fc8f40`): forgot/reset password now exists on
  tenant subdomains, not just the agency login.
- **Database-backed theme library + Save-as-Template** — done (`b8c6456`, `397614a`):
  themes are a real DB table with admin CRUD; any live tenant's site can be cloned
  into a reusable theme in one click; base templates are discovered from disk
  automatically.
- **Add-on marketplace payment gating** — done (`b8c6456`, `fe2b9c8`): a client
  request no longer self-activates. It goes to "pending," and only an explicit admin
  Activate/Deactivate action (`AdminTenantAddonController`) flips it — matches the
  "admin-confirmed manual activation" pattern, since billing isn't tied to Razorpay.
- **Lead → Client → Tenant connection** — done (`c4ba6c3`): a Client's page now shows
  a "Website" card — one-click "Create Tenant Site," prefilled from the Client record.
- **System Health page** — done (`56a06b9`): recent error-log entries surfaced in
  Super Admin. (Note: this is a log viewer, not push alerting — see item 3 below,
  which is still partially open.)

---

## 🔴 Critical — real risk right now

1. **Add-on activation doesn't gate any real feature.** The payment/request flow above
   is solved — this is a *different* gap. `AdminTenantAddonController::activate` only
   flips `TenantAddon.status`; there is no `tenantHasFeature()` check anywhere in the
   codebase that changes actual tenant behavior. A client who gets an add-on activated
   today receives an "Active" badge and nothing else. **Do not sell add-ons as working
   features until at least one is wired to a real gated feature** (see Phase 16 below).
2. **No error monitoring/alerting.** System Health (above) is a pull-based log viewer
   you have to remember to check — still no push alert (Sentry/Bugsnag/Flare or
   equivalent) when something breaks on a tenant's storefront.

## 🟠 High — core commerce gaps, matters before real customers rely on this

3. **Real end-to-end Razorpay payment never fully completed.** Code is correct
   (Checkout.js + real HMAC-SHA256 webhook verification, per-tenant keys) but never
   fired against a live/test Razorpay account with real credentials.
4. **No recurring billing.** One-time checkout only — no automatic recurring charge,
   retry, or dunning for tenant-sold subscriptions or your own plan/add-on billing.
5. **No shipping cost calculation.** Address is free text; no rate calc, courier
   integration, or shipping zones.
6. **No refund/cancellation flow.** No way to process a refund beyond changing an
   order's status label.
7. **Customer accounts are guest-only** — deliberate for v1, still true.

## 🟡 Medium — expands what the platform can do, not urgent

8. **Only one real business vertical (Shopping).** Restaurant, School, and
   Portfolio/Business don't exist as modules, tables, or storefront templates — see
   the forward roadmap below (Phases 15-17).
9. **Theme customizer is minimal** — accent color + 3 section toggles, not real
   layout/section reordering.
10. **Theme thumbnail images don't exist** — gallery falls back to a text placeholder.
11. **No self-serve signup** — parked, correctly, until add-on billing (now proven)
    extends to "does this person get a tenant at all."
12. **No custom domain support** for tenants wanting their own `.com`.
13. **No automated tenant suspension for non-payment** — 100% manual today.
14. **No automated billing reminders for tenant plans/add-ons** — the old CRM
    `Subscription` model has this; not connected to the new `Tenant`/`TenantAddon`
    system.
15. **No cross-tenant revenue/business dashboard** — no aggregate MRR/churn view.
16. **No roles/permissions system.** One flat admin login, one flat tenant-owner
    login. Blocks School (needs parent/teacher/student roles) — see Phase 17.
17. **No client impersonation** ("Login as Client/Tenant" for support).
18. **No lightweight portal for old-style `Client` records** (own website, not a
    Shopping tenant) — they cannot log in for anything today, not even to see their
    own invoice/subscription status. Confirmed as a real, distinct requirement
    2026-07-14 — see Phase 18.
19. **No support tickets.**

## 🟢 Low — polish, do last

20. **No audit log** of admin actions beyond the timestamp.
21. **No staging environment** — testing happens via disposable test tenants directly
    on production.
22. **No in-dashboard help/onboarding content** for tenant users.

---

## Forward roadmap — new business verticals + platform maturity (Phase 15+)

Continues the numbering in `MULTI_TENANT_BUILD_PLAN.md` / `PHASE_PROMPTS_STANDALONE.md`
(Phases 0-14 already built). Full prompts for each live in
`PHASE_PROMPTS_STANDALONE.md`.

- **Phase 15 — Restaurant module. ✅ DONE (2026-07-14).** Second business
  vertical, verified end-to-end on production. Menu reuses TenantProduct
  (grouped by category), Orders reuses the existing fulfillment lifecycle, a
  new tenant-scoped Reservations table + dashboard CRUD + public
  Book-a-Table form. site_type gained a third value 'restaurant';
  storefront template auto-discovered. Commits: 95bda6b, 9de567c, 769e9ba.
  Proved the module system generalizes to a real second industry with zero
  regression on existing Shopping/Info tenants.
- **Phase 16 — Make one add-on real.** Pick the highest-demand add-on, wire an actual
  `tenantHasFeature()` gate into real behavior, prove the mechanism end-to-end before
  selling more add-ons as if they work.
- **Phase 17 — Portfolio/Business module.** Mostly static content (pages, services,
  testimonials, gallery, blog) — no payment/order complexity, fastest to ship.
- **Phase 18 — Lightweight Client portal tier.** A scoped login for old-style
  `Client` records — visibility into their own plan/invoice/subscription status only,
  zero Shopping/e-commerce surface. New auth path, since `Client` has no login at all
  today and `Tenant` login drags in the whole e-commerce app.
- **Phase 19 — Roles & permissions.** Needed before School (multi-role logins) and
  useful for impersonation/support-ticket ownership too.
- **Phase 20 — School module.** Most complex of the four verticals (Students,
  Teachers, Classes, Admissions, multi-role parent/teacher access) — depends on
  Phase 19, deliberately last.
- **Later / explicitly not yet scoped**: operational polish pulled forward as real
  pain points appear (impersonation, real custom-domain+SSL, support tickets, backup
  restore UI); Theme SDK/portable package format, public theme/add-on marketplace,
  visual theme builder, AI content/SEO/chatbot — all "hundreds of clients" scale
  features, intentionally not started before the four verticals + real add-ons exist.

## Recommended order right now

1. Phase 15 (Restaurant module) — DONE 2026-07-14
2. Phase 16 (real add-on gating) — before selling any more add-ons
3. A full real Razorpay test-mode payment, start to finish (blocked on Razorpay
   account/keys)
4. Phase 17-20, in order, as scoped above
