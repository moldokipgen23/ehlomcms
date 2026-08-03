<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantAbandonedCart;
use App\Models\TenantCustomer;
use App\Models\TenantFeatureSetting;
use App\Models\TenantLoyaltyTransaction;
use App\Models\TenantOrder;
use App\Models\TenantProduct;
use App\Models\TenantSubscriptionPlan;
use App\Models\TenantVendor;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantCommerceFeatureController extends Controller
{
    private array $features = [
        'gst_invoice' => 'GST Invoice',
        'abandoned_cart' => 'Abandoned Cart Recovery',
        'loyalty_rewards' => 'Loyalty & Rewards',
        'subscription_products' => 'Subscription Products',
        'advanced_analytics' => 'Advanced Store Analytics',
        'bulk_import_export' => 'Bulk Import / Export',
        'multi_vendor' => 'Multi-Vendor Marketplace',
        'pos_integration' => 'POS Integration',
    ];

    public function show(string $feature): View
    {
        $tenant = app(TenantContext::class)->get();
        abort_unless(isset($this->features[$feature]) && $tenant->hasModule($feature), 404);
        $setting = TenantFeatureSetting::firstOrCreate(['tenant_id' => $tenant->id, 'feature_key' => $feature], ['settings' => [], 'is_active' => true]);

        $data = match ($feature) {
            'advanced_analytics' => [
                'orders' => TenantOrder::where('tenant_id', $tenant->id)->count(),
                'revenue' => TenantOrder::where('tenant_id', $tenant->id)->sum('total'),
                'customers' => TenantCustomer::where('tenant_id', $tenant->id)->count(),
                'products' => TenantProduct::where('tenant_id', $tenant->id)->count(),
                'recentOrders' => TenantOrder::where('tenant_id', $tenant->id)->latest()->limit(8)->get(),
            ],
            'abandoned_cart' => ['carts' => TenantAbandonedCart::where('tenant_id', $tenant->id)->latest()->get()],
            'loyalty_rewards' => ['transactions' => TenantLoyaltyTransaction::where('tenant_id', $tenant->id)->with('customer')->latest()->get()],
            'subscription_products' => ['plans' => TenantSubscriptionPlan::where('tenant_id', $tenant->id)->with('product')->latest()->get(), 'products' => TenantProduct::where('tenant_id', $tenant->id)->orderBy('name')->get()],
            'multi_vendor' => ['vendors' => TenantVendor::where('tenant_id', $tenant->id)->latest()->get()],
            default => [],
        };

        return view('tenant.commerce-feature.show', ['tenant' => $tenant, 'feature' => $feature, 'label' => $this->features[$feature], 'setting' => $setting, 'data' => $data]);
    }

    public function update(Request $request, string $feature): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_unless(isset($this->features[$feature]) && $tenant->hasModule($feature), 404);

        if ($feature === 'multi_vendor') {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:40'],
                'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            ]);
            TenantVendor::create($data + ['tenant_id' => $tenant->id, 'is_active' => true]);
            return back()->with('success', 'Vendor added.');
        }

        if ($feature === 'subscription_products') {
            $data = $request->validate([
                'tenant_product_id' => ['nullable', 'integer'],
                'name' => ['required', 'string', 'max:120'],
                'interval' => ['required', 'in:weekly,monthly,quarterly,yearly'],
                'price' => ['required', 'numeric', 'min:0'],
            ]);
            if (!empty($data['tenant_product_id'])) {
                abort_unless(TenantProduct::where('tenant_id', $tenant->id)->whereKey($data['tenant_product_id'])->exists(), 404);
            }
            TenantSubscriptionPlan::create($data + ['tenant_id' => $tenant->id, 'is_active' => true]);
            return back()->with('success', 'Subscription plan added.');
        }

        if ($feature === 'loyalty_rewards') {
            TenantFeatureSetting::updateOrCreate(
                ['tenant_id' => $tenant->id, 'feature_key' => $feature],
                ['is_active' => $request->boolean('is_active'), 'settings' => $request->only(['points_per_100', 'redeem_value'])]
            );
            return back()->with('success', 'Loyalty settings saved.');
        }

        TenantFeatureSetting::updateOrCreate(
            ['tenant_id' => $tenant->id, 'feature_key' => $feature],
            ['is_active' => $request->boolean('is_active'), 'settings' => $request->except(['_token', 'is_active'])]
        );

        return back()->with('success', $this->features[$feature] . ' settings saved.');
    }

    public function exportProducts(string $feature)
    {
        $tenant = app(TenantContext::class)->get();
        abort_unless(in_array($feature, ['bulk_import_export', 'pos_integration'], true) && $tenant->hasModule($feature), 404);
        $rows = TenantProduct::where('tenant_id', $tenant->id)->orderBy('name')->get(['name', 'sku', 'price', 'stock', 'is_active', 'category']);
        $csv = "name,sku,price,stock,is_active,category\n";
        foreach ($rows as $row) {
            $csv .= sprintf("\"%s\",\"%s\",%s,%s,%s,\"%s\"\n", str_replace('"', '""', $row->name), str_replace('"', '""', (string) $row->sku), $row->price, $row->stock, $row->is_active ? 1 : 0, str_replace('"', '""', (string) $row->category));
        }

        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="products.csv"']);
    }

    public function importProducts(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('bulk_import_export'), 404);
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt']]);
        $handle = fopen($request->file('csv')->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            if (!$data || empty($data['name'])) {
                continue;
            }
            $sku = trim((string) ($data['sku'] ?? ''));
            $match = ['tenant_id' => $tenant->id, 'sku' => $sku !== '' ? $sku : 'import-' . Str::slug($data['name'])];
            TenantProduct::updateOrCreate(
                $match,
                [
                    'name' => $data['name'] ?? 'Imported Product',
                    'slug' => Str::slug($data['name'] ?? 'imported-product') . '-' . uniqid(),
                    'price' => (float) ($data['price'] ?? 0),
                    'stock' => (int) ($data['stock'] ?? 0),
                    'is_active' => (bool) ($data['is_active'] ?? true),
                    'category' => $data['category'] ?? null,
                ]
            );
            $count++;
        }
        fclose($handle);

        return back()->with('success', $count . ' products imported.');
    }
}
