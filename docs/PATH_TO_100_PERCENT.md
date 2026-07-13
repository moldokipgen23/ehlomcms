# Path to a fully complete SaaS platform

Everything remaining, honestly categorized. Verified against the actual code/server
where noted — not guessed.

---

## 🔴 Critical — real risk right now, fix before anything else

1. **No automated database backups.** Verified: the only cron job on the VPS is the
   Laravel scheduler (`schedule:run`). Every backup so far has been a manual
   `mysqldump` I ran by hand before each deploy. If the server has a problem between
   deploys, there is currently no safety net at all. Needs: a daily automated backup
   cron, ideally shipped somewhere off the VPS itself (not just sitting in `/tmp`).
2. **No tenant password reset.** Verified: `routes/tenant.php` has zero forgot-password
   routes. If a real client forgets their dashboard password, there is currently no way
   for them to recover it themselves — only you, manually, via direct database access.
   The agency's own Breeze login already has this; tenants don't.
3. **No error monitoring/alerting.** Verified: no Sentry/Bugsnag/Flare configured,
   nothing in `.env.example`. If something breaks on a tenant's storefront at 3am, you
   find out only when the client complains — not before.

## 🟠 High — core commerce gaps, matters before real customers rely on this

4. **Real end-to-end Razorpay payment never fully completed.** Verified working: the
   checkout modal opens correctly with the right amount. Never verified: an actual
   successful payment captured, webhook received, order marked paid, on real (even
   test-mode) Razorpay credentials.
5. **No recurring billing.** Current Razorpay integration is one-time checkout only. A
   tenant's own subscription-style products (if they sell subscriptions) or your own
   plan/add-on billing have no automatic recurring charge, retry, or dunning — this was
   researched and scoped conceptually but not built.
6. **No shipping cost calculation.** Shipping address is captured as free text; there's
   no rate calculation, courier integration, or shipping zones. Every order currently
   assumes shipping is handled entirely outside the system.
7. **No refund/cancellation flow.** Once an order is placed, there's no way to process a
   refund or formally cancel it beyond changing its status label.
8. **Customer accounts are guest-only.** No login, no saved addresses, no order history
   for a tenant's own shoppers — deliberately scoped this way for v1, but worth being
   explicit that it's still true.

## 🟡 Medium — expands what the platform can do, not urgent

9. **Only 2 real templates** (Shop, Info). Restaurant/school/church module presets are
   architecturally supported but nothing built for them yet.
10. **Theme customizer is minimal** — accent color + 3 section toggles. Real page-builder
    style customization (reordering sections, custom fonts, layout variants) doesn't
    exist.
11. **Theme thumbnail images don't actually exist** — the theme registry references
    image paths (`images/themes/shop-preview.png`) that were never created; the gallery
    currently falls back to a text placeholder.
12. **No self-serve signup** (pick a plan, pay, get a tenant automatically) — parked
    until manual add-on billing (just built) proves out.
13. **No custom domain support** for tenants using their own `.com`.
14. **No automated tenant suspension for non-payment** — suspend/activate is 100% manual
    today.
15. **No automated billing reminders for tenant plans/add-ons** — the OLD CRM
    `Subscription` model has this (`SendRenewalReminders`), but it's not connected to
    the new `Tenant`/`TenantAddon` system at all.
16. **Lead → Client → Tenant isn't connected.** Converting a Lead to a Client doesn't
    create or link a Tenant; that's still a fully separate manual step.
17. **No cross-tenant revenue/business dashboard** for you — no aggregate view of total
    MRR, active tenants, churn, etc.

## 🟢 Low — polish, do last

18. **No audit log** of admin actions (who activated which add-on, when, beyond the
    timestamp) — no "actor" tracking.
19. **No staging environment** — all testing happens via disposable test tenants
    directly on production. Works, but riskier than a proper separate staging copy.
20. **No in-dashboard help/onboarding content** for tenant users — they'd need you to
    walk them through it manually the first time.

---

## Recommended order

1. Automated backups + tenant password reset (both cheap, both real risk reduction)
2. Error monitoring (cheap to add, changes how fast you find out about problems)
3. A full real Razorpay test-mode payment, start to finish
4. Everything else, prioritized by what you actually plan to sell next
