<?php

namespace App\Console\Commands;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\InvoicePaymentLinkService;
use App\Services\MailConfigService;
use App\Services\InvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
    public function handle(InvoiceService $service, InvoicePaymentLinkService $paymentLinks): int
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

            $invoice = null;
            DB::transaction(function () use (&$invoice, $service, $subscription, $amount, $productName) {
                $invoice = Invoice::create([
                    'invoice_number' => $service->nextInvoiceNumber(),
                    'client_id' => $subscription->client_id,
                    'subtotal' => $amount,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'tax' => 0,
                    'total' => $amount,
                    'due_date' => now()->addDays(7),
                    'status' => 'unpaid',
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
            $this->sendInvoiceNotification($invoice, $subscription, $paymentLinks);
            $line = "Renewal invoice created for {$subscription->client?->name} — {$productName} (₹" . number_format($amount, 2) . ')';
            $this->line($line);
            Log::info('[invoices:generate-renewals] ' . $line);
        }

        $this->info("Done. {$created} draft renewal invoice(s) created.");
        Log::info("[invoices:generate-renewals] {$created} draft renewal invoice(s) created.");

        return self::SUCCESS;
    }

    private function sendInvoiceNotification(Invoice $invoice, Subscription $subscription, InvoicePaymentLinkService $paymentLinks): void
    {
        if (! $invoice->client?->email || ! MailConfigService::configured()) {
            return;
        }

        try {
            MailConfigService::apply();
            Mail::to($invoice->client->email)->send(new InvoiceMail($invoice->loadMissing('client', 'items')));
            // Prevent the separate reminder command from sending a duplicate
            // message for the same renewal cycle.
            $subscription->update(['last_reminder_sent_at' => now()]);
            Log::info('[invoices:generate-renewals] Renewal invoice emailed', [
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'payment_link' => $paymentLinks->make($invoice),
            ]);
        } catch (\Throwable $e) {
            Log::error('[invoices:generate-renewals] Renewal invoice email failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
