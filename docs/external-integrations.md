# External Product Integrations

The portal is the commercial control plane. External products remain independent applications and expose a small, read-only billing/catalog contract to the portal.

## Add a product

1. Open **ERP Integrations** in the portal admin.
2. Create an integration with a name, driver, and base URL.
3. Add the integration key in the credential field. Credentials are encrypted in the portal database.
4. Use the default paths or provide overrides for the external product's catalog, accounts, subscriptions, and invoices endpoints.
5. Save and click **Sync now**.

The scheduler also syncs active integrations every 15 minutes. A signed webhook endpoint is available at:

`POST /api/integrations/{integration}/webhook`

Polling remains the fallback when an external product does not send webhooks.

## What is mirrored

- Published product plans and prices
- External customer/account records
- Active and historical subscriptions
- Invoices and payment status
- Last sync status and errors

The portal links an external account to an existing client by email or phone when a match is available. It does not copy operational student, teacher, restaurant, or customer records.

Imported totals appear in **Revenue** under **External ERP billing**, separate from Ehlom-owned subscriptions. The AI workflow offer catalog also receives active imported plans, so lead scoring can recommend an external ERP plan alongside Ehlom website products.

## Current eSchool connection

`eschool.ehlom.com` implements:

- `GET /api/v1/integrations/health`
- `GET /api/v1/integrations/catalog`
- `GET /api/v1/integrations/accounts`
- `GET /api/v1/integrations/subscriptions`
- `GET /api/v1/integrations/invoices`

These routes use a dedicated `X-Integration-Key`, separate from school user authentication.

## Future ERP adapters

A restaurant ERP can use the same four resource responses and `generic_api`, or receive a named adapter when its schema differs. The adapter boundary is `ExternalIntegrationAdapter`, so the portal catalog, revenue, AI offers, sync status, and webhook handling remain reusable.

## Important boundary

The current contract is read-only. It supports central catalog visibility, subscription tracking, invoice reporting, and sales recommendations. A true “buy in the portal and provision in the external ERP” flow requires each ERP to expose authenticated write endpoints for account creation, plan purchase, payment confirmation, cancellation, and renewal. Those endpoints should be added per ERP before enabling automated fulfillment.
