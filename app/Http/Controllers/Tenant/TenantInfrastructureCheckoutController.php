<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\TenantAddon;
use App\Services\InvoiceAutoGenerator;
use App\Services\InvoicePaymentLinkService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantInfrastructureCheckoutController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();

        $domains = Product::where('category', 'domain')->where('status', 'active')->orderBy('price')->get();
        $hosting = Product::where('category', 'hosting')->where('status', 'active')->orderBy('price')->get();
        $tenant->loadMissing('hostingPlan', 'client');

        $client = $tenant->client;
        $addons = TenantAddon::with('addonMeta')
            ->where('tenant_id', $tenant->id)
            ->whereHas('addonMeta', fn ($query) => $query->whereNull('module_key'))
            ->orderByRaw("status = 'active' desc")
            ->latest('activated_at')
            ->get();

        $clientProducts = collect();
        $subscriptions = collect();
        $invoices = collect();

        if ($client) {
            $clientProducts = $client->products()
                ->orderBy('category')
                ->orderBy('name')
                ->get();

            $subscriptions = Subscription::with('product')
                ->where('client_id', $client->id)
                ->orderByRaw("status = 'active' desc")
                ->orderBy('expiry_date')
                ->get();

            $invoices = Invoice::with('items')
                ->where('client_id', $client->id)
                ->latest()
                ->limit(10)
                ->get();
        }

        $invoicePaymentLinks = $invoices
            ->filter(fn (Invoice $invoice) => in_array($invoice->status, ['unpaid', 'partial', 'overdue'], true))
            ->mapWithKeys(fn (Invoice $invoice) => [
                $invoice->id => app(InvoicePaymentLinkService::class)->make($invoice),
            ]);

        return view('tenant.infrastructure.index', compact('tenant', 'domains', 'hosting', 'addons', 'clientProducts', 'subscriptions', 'invoices', 'invoicePaymentLinks'));
    }

    public function create(Product $product, InvoiceAutoGenerator $invoiceGenerator, InvoicePaymentLinkService $paymentLinks): RedirectResponse
    {
        if (!in_array($product->category, ['domain', 'hosting'])) {
            abort(404);
        }

        $tenant = app(TenantContext::class)->get();

        if (! $tenant->client) {
            return back()->with('error', 'This store is not linked to a client billing account yet. Please contact support.');
        }

        $reference = "tenant:{$tenant->id};product:{$product->id}";
        $invoice = Invoice::where('client_id', $tenant->client_id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->where('notes', 'like', '%' . $reference . '%')
            ->latest('id')
            ->first();

        if (! $invoice) {
            // InvoiceAutoGenerator applies GST to the base product price.
            $invoice = $invoiceGenerator->forInfrastructure(
                $tenant,
                $product->name,
                (float) $product->price,
                $product->category,
                $reference,
            );
        }

        if (! $invoice) {
            return back()->with('error', 'We could not create the Ehlom invoice. Please contact support.');
        }

        return redirect()->to($paymentLinks->make($invoice));
    }

    public function requestCustomDomain(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'custom_domain' => ['required', 'string', 'max:255'],
        ]);

        $domain = Str::of($data['custom_domain'])
            ->lower()
            ->replaceMatches('/^https?:\/\//', '')
            ->replaceMatches('/\/.*$/', '')
            ->replaceMatches('/:\d+$/', '')
            ->trim()
            ->value();

        $domain = ltrim($domain, '.');

        if (! preg_match('/^(?!-)(?:[a-z0-9-]{1,63}\.)+[a-z]{2,}$/', $domain)) {
            return back()
                ->withInput()
                ->with('error', 'Enter a valid domain name, for example jemdesign.com or shop.jemdesign.com.');
        }

        $tenant = app(TenantContext::class)->get();

        $tenant->update([
            'custom_domain' => $domain,
            'domain_status' => 'pending',
            'domain_verified_at' => null,
        ]);

        return back()->with('success', 'Custom domain request saved. Add the DNS record shown below, then Ehlom can verify and issue SSL.');
    }

    public function checkout(Request $request, Product $product): RedirectResponse
    {
        if (!in_array($product->category, ['domain', 'hosting'])) {
            abort(404);
        }

        $tenant = app(TenantContext::class)->get();
        return $this->create($product, app(InvoiceAutoGenerator::class), app(InvoicePaymentLinkService::class));
    }

    public function success(): RedirectResponse
    {
        // Legacy callback route: never grant a product from query-string data.
        return redirect()->route('tenant.infrastructure')->with('error', 'Please use the Ehlom invoice payment link to complete this purchase.');
    }
}
