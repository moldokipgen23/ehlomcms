<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\InvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateRenewalInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create draft renewal invoices for subscriptions expiring soon';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceService $service): int
    {
        $subscriptions = Subscription::dueForRenewalInvoice()
            ->with(['client', 'product'])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions due for a renewal invoice.');

            return self::SUCCESS;
        }

        $created = 0;

        foreach ($subscriptions as $subscription) {
            $amount = (float) $subscription->renewal_amount;
            $productName = $subscription->product->name ?? 'Subscription';

            DB::transaction(function () use ($service, $subscription, $amount, $productName) {
                $invoice = Invoice::create([
                    'invoice_number' => $service->nextInvoiceNumber(),
                    'client_id' => $subscription->client_id,
                    'subtotal' => $amount,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'tax' => 0,
                    'total' => $amount,
                    'due_date' => now()->addDays(7),
                    'status' => 'draft',
                    'notes' => 'Auto-generated renewal invoice for subscription #' . $subscription->id . '.',
                ]);

                $invoice->items()->create([
                    'description' => 'Renewal — ' . $productName,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total' => $amount,
                ]);

                $subscription->update(['last_invoiced_at' => now()]);
            });

            $created++;
            $line = "Draft invoice created for {$subscription->client?->name} — {$productName} (₹" . number_format($amount, 2) . ')';
            $this->line($line);
            Log::info('[invoices:generate-renewals] ' . $line);
        }

        $this->info("Done. {$created} draft renewal invoice(s) created.");
        Log::info("[invoices:generate-renewals] {$created} draft renewal invoice(s) created.");

        return self::SUCCESS;
    }
}
