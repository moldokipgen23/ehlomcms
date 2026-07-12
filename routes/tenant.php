<?php

// Tenant-scoped routes (subdomain-based).
// Routes here are only reachable when a tenant has been resolved by
// ResolveTenant middleware (see app/Http/Middleware/ResolveTenant.php).
// These routes run INSIDE a tenant context — TenantContext::get() is
// guaranteed to return a valid, active tenant.
//
// Local testing:
//   Add entries to /etc/hosts:
//     127.0.0.1 testshop1.ehlom-os.test
//     127.0.0.1 testinfo1.ehlom-os.test
//   Then set APP_TENANT_DOMAIN=ehlom-os.test in .env
//   Visit http://testshop1.ehlom-os.test in your browser.
//
//   Alternatively, use Herd/Valet with `herd link ehlom-os --secure`
//   and access tenant subdomains directly.
//
// Phase 3+ will add actual routes here.
