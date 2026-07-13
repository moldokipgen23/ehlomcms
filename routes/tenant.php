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

use App\Http\Controllers\Tenant\Auth\TenantLoginController;
use App\Http\Controllers\Tenant\TenantCartController;
use App\Http\Controllers\Tenant\TenantCatalogController;
use App\Http\Controllers\Tenant\TenantContentController;
use App\Http\Controllers\Tenant\TenantDashboardController;
use App\Http\Controllers\Tenant\TenantHomeController;
use App\Http\Controllers\Tenant\TenantOrderController;
use App\Http\Controllers\Tenant\TenantPaymentSettingsController;
use App\Http\Controllers\Tenant\TenantSettingsController;
use App\Http\Controllers\Tenant\TenantThemeController;
use App\Http\Controllers\Tenant\TenantTrackController;
use Illuminate\Support\Facades\Route;

// Domain-scoped to genuine tenant subdomains only. Without this, these routes
// share literal URIs ('/', '/dashboard') with agency-only routes in web.php,
// and Laravel's route collection would evict whichever was registered second
// for a given method+uri — this domain constraint gives tenant routes a
// distinct collection key AND stops them from ever matching portal.ehlom.com,
// www.ehlom.com, or the bare domain, regardless of registration order.
Route::domain('{subdomain}.' . config('app.tenant_domain', 'ehlom.com'))
    ->where(['subdomain' => '(?!portal$|www$)[a-z0-9-]+'])
    ->group(function () {

Route::middleware('tenant')->group(function () {
    Route::get('/', [TenantHomeController::class, 'index'])->name('tenant.home');

    // Cart + Checkout (public storefront)
    // Deliberately plain {id} parameters, not Eloquent route-model binding -
    // see TenantCartController for why (domain-scoped routes break implicit
    // binding, and binding without a manual tenant_id check would resolve
    // ANY tenant's product/order by ID regardless of who owns it).
    Route::get('cart', [TenantCartController::class, 'index'])->name('tenant.cart');
    Route::post('cart/add/{id}', [TenantCartController::class, 'add'])->name('tenant.cart.add');
    Route::post('cart/update/{id}', [TenantCartController::class, 'update'])->name('tenant.cart.update');
    Route::post('cart/remove/{id}', [TenantCartController::class, 'remove'])->name('tenant.cart.remove');
    Route::get('checkout', [TenantCartController::class, 'checkout'])->name('tenant.checkout');
    Route::post('checkout', [TenantCartController::class, 'placeOrder'])->name('tenant.checkout.place');
    Route::get('checkout/pay/{id}', [TenantCartController::class, 'pay'])->name('tenant.checkout.pay');
    Route::get('checkout/confirm/{id}', [TenantCartController::class, 'confirm'])->name('tenant.checkout.confirm');

    // Order tracking (public)
    Route::get('track', [TenantTrackController::class, 'show'])->name('tenant.track');
    Route::get('track/lookup', [TenantTrackController::class, 'lookup'])->name('tenant.track.lookup');
});

Route::middleware('tenant')->prefix('dashboard')->group(function () {

    // Guest routes. No public registration - a tenant's owner account is
    // created by the agency (AdminTenantController::store) at the same time
    // the tenant itself is created, not via self-signup. Leaving a public
    // register route on every subdomain would let any visitor create a
    // dashboard login for someone else's shop.
    Route::middleware('guest')->group(function () {
        Route::get('login', [TenantLoginController::class, 'create'])->name('tenant.login');
        Route::post('login', [TenantLoginController::class, 'store']);
    });

    // Authenticated tenant routes
    Route::middleware('tenant.auth')->group(function () {
        Route::get('/', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');

        Route::get('settings', [TenantSettingsController::class, 'edit'])->name('tenant.settings');
        Route::post('settings', [TenantSettingsController::class, 'update']);
        Route::post('settings/logo', [TenantSettingsController::class, 'uploadLogo'])->name('tenant.settings.logo');
        Route::post('settings/banner', [TenantSettingsController::class, 'uploadBanner'])->name('tenant.settings.banner');

        Route::get('content', [TenantContentController::class, 'index'])->name('tenant.content');
        Route::post('content/about', [TenantContentController::class, 'updateAbout'])->name('tenant.content.about');
        Route::post('content/contact', [TenantContentController::class, 'updateContact'])->name('tenant.content.contact');
        Route::post('content/gallery', [TenantContentController::class, 'storeGalleryImage'])->name('tenant.gallery.store');
        Route::delete('content/gallery/{id}', [TenantContentController::class, 'destroyGalleryImage'])->name('tenant.gallery.destroy');

        // Catalog (Products CRUD)
        Route::get('catalog', [TenantCatalogController::class, 'index'])->name('tenant.catalog');
        Route::get('catalog/create', [TenantCatalogController::class, 'create'])->name('tenant.catalog.create');
        Route::post('catalog', [TenantCatalogController::class, 'store'])->name('tenant.catalog.store');
        Route::get('catalog/{id}/edit', [TenantCatalogController::class, 'edit'])->name('tenant.catalog.edit');
        Route::put('catalog/{id}', [TenantCatalogController::class, 'update'])->name('tenant.catalog.update');
        Route::delete('catalog/{id}', [TenantCatalogController::class, 'destroy'])->name('tenant.catalog.destroy');

        // Payment Settings (only accessible when action_type=razorpay)
        Route::get('payments', [TenantPaymentSettingsController::class, 'edit'])->name('tenant.payments');
        Route::post('payments', [TenantPaymentSettingsController::class, 'update']);

        // Orders
        Route::get('orders', [TenantOrderController::class, 'index'])->name('tenant.orders');
        Route::post('orders/{id}/status', [TenantOrderController::class, 'updateStatus'])->name('tenant.orders.update-status');

        // Theme customizer
        Route::get('customize', [TenantThemeController::class, 'edit'])->name('tenant.theme');
        Route::post('customize', [TenantThemeController::class, 'update'])->name('tenant.theme.update');

        // Add-on marketplace
        Route::get('addons', [\App\Http\Controllers\Tenant\TenantAddonController::class, 'index'])->name('tenant.addons');
        Route::post('addons/toggle/{addonKey}', [\App\Http\Controllers\Tenant\TenantAddonController::class, 'toggle'])->name('tenant.addons.toggle');
    });

    // Logout (POST, no auth middleware since session is still valid)
    Route::post('logout', [TenantLoginController::class, 'destroy'])->name('tenant.logout');
});

}); // end Route::domain('{subdomain}.'.config('app.tenant_domain'))
