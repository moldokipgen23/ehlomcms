<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\URL;

class InvoicePaymentLinkService
{
    /**
     * Create the public, signed payment URL used by email and client billing.
     * The URL is intentionally hosted on the Ehlom billing portal, because
     * these invoices belong to Ehlom rather than to a tenant's storefront.
     */
    public function make(Invoice $invoice, int $days = 30): string
    {
        return URL::temporarySignedRoute(
            'billing.invoices.pay',
            now()->addDays($days),
            [
                'portalHost' => parse_url(config('app.url'), PHP_URL_HOST) ?: 'portal.ehlom.com',
                'invoice' => $invoice,
            ],
        );
    }
}
