# Lead Sources

The portal treats lead discovery as a separate system from external ERP integrations.

## Source roles

### Hola Business Directory

Hola is the preferred first-party source for the businesses already collected and maintained in the Hola product. The portal uses Hola's read-only partner endpoint:

`GET https://hola.ehlom.com/api/v1/businesses`

The connection uses a scoped integration key with `businesses:read`. The key is stored encrypted in the portal and is never shown after saving. Hola is the best default for repeatable imports because the data model, ownership, and access rules are controlled by Ehlom.

### Google Places

Google Places is the discovery and gap-filling source. Add it from **Lead Generation > Lead Sources**, provide a Google Places API key, and enter one or more search queries such as `fashion boutiques in Churachandpur` or `schools in Imphal`. The connector uses the official Text Search (New) API, not HTML scraping:

`POST https://places.googleapis.com/v1/places:searchText`

Google requires billing and an explicit field mask. Keep the field mask limited to the fields the sales team actually needs, because Places pricing is field based. Use Google for targeted searches and use Hola for the maintained directory import.

## Recommended operating model

1. Sync Hola first for the known directory.
2. Use Google Places for locations or categories missing from Hola.
3. Review duplicates and incomplete contact details before outreach.
4. Let the AI workflow research, score, and draft a message from the imported lead record.
5. Require approval before WhatsApp or email is sent, and record opt-outs and replies.

The AI does not receive unrestricted access to either source. It receives the normalized lead, source metadata, and approved enrichment fields from the portal. This keeps the workflow auditable and allows another directory or ERP to be added later without changing the AI workflow.

## Adding a source

Open `/lead-sources` in the admin portal:

- **Hola Business Directory**: active, read-only, scheduled sync optional.
- **Google Places**: add the API key, queries, region code, and page size; leave automatic sync off until the expected query volume and budget are reviewed.

Every imported lead stores `lead_source_id`, `external_id`, `external_metadata`, and `last_synced_at`. Matching prefers the source ID, then website, then phone number. Imported records are kept if a source is removed.

## References

- Google Places Text Search (New): https://developers.google.com/maps/documentation/places/web-service/text-search
- Google Maps Platform pricing: https://developers.google.com/maps/billing-and-pricing/pricing
- Google Maps Platform usage and billing: https://developers.google.com/maps/documentation/places/web-service/usage-and-billing
