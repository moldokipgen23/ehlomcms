<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class InvoiceAutoGenerator
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Auto-generate an invoice for a domain/hosting purchase.
     */
    public function forInfrastructure(Tenant $tenant, string $productName, float $price, string $category, ?string $reference = null): ?Invoice
    {
        $client = $tenant->client;
        if (!$client) {
            Log::warning('InvoiceAutoGenerator: no client for tenant', ['tenant_id' => $tenant->id]);
            return null;
        }

        return $this->createInvoice($client, [
            [
                'description' => ucfirst($category) . ': ' . $productName,
                'quantity' => 1,
                'unit_price' => $price,
            ],
        ], $category, $reference);
    }

    /**
     * Auto-generate an invoice for an add-on subscription.
     */
    public function forAddon(Tenant $tenant, string $addonName, float $price): ?Invoice
    {
        $client = $tenant->client;
        if (!$client) {
            Log::warning('InvoiceAutoGenerator: no client for tenant add-on', ['tenant_id' => $tenant->id]);
            return null;
        }

        return $this->createInvoice($client, [
            [
                'description' => 'Add-on: ' . $addonName,
                'quantity' => 1,
                'unit_price' => $price,
            ],
        ], 'addon');
    }

    private function createInvoice(Client $client, array $lineItems, string $category, ?string $reference = null): Invoice
    {
        $taxRate = 18; // GST

        $built = $this->invoiceService->buildLineItems($lineItems, $taxRate);

        $invoice = Invoice::create([
            'invoice_number' => $this->invoiceService->nextInvoiceNumber(),
            'client_id' => $client->id,
            'subtotal' => $built['subtotal'],
            'tax_rate' => $taxRate,
            'tax_amount' => $built['tax_amount'],
            'tax' => $built['tax_amount'],
            'total' => $built['total'],
            'due_date' => now()->addDays(7),
            'status' => 'unpaid',
            'notes' => 'Auto-generated for ' . $category . ' purchase' . ($reference ? ' [' . $reference . ']' : ''),
        ]);

        $invoice->items()->createMany($built['items']);

        Log::info('InvoiceAutoGenerator: invoice created', [
            'invoice_id' => $invoice->id,
            'client_id' => $client->id,
            'total' => $invoice->total,
            'category' => $category,
        ]);

        return $invoice;
    }
}
