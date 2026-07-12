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
use App\Http\Controllers\Tenant\Auth\TenantRegisteredUserController;
use App\Http\Controllers\Tenant\TenantCatalogController;
use App\Http\Controllers\Tenant\TenantContentController;
use App\Http\Controllers\Tenant\TenantDashboardController;
use App\Http\Controllers\Tenant\TenantHomeController;
use App\Http\Controllers\Tenant\TenantOrderController;
use App\Http\Controllers\Tenant\TenantPaymentSettingsController;
use App\Http\Controllers\Tenant\TenantSettingsController;
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
});

Route::middleware('tenant')->prefix('dashboard')->group(function () {

    // Guest routes (login, register)
    Route::middleware('guest')->group(function () {
        Route::get('login', [TenantLoginController::class, 'create'])->name('tenant.login');
        Route::post('login', [TenantLoginController::class, 'store']);
        Route::get('register', [TenantRegisteredUserController::class, 'create'])->name('tenant.register');
        Route::post('register', [TenantRegisteredUserController::class, 'store']);
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

        // Orders (read-only, only accessible when action_type=razorpay)
        Route::get('orders', [TenantOrderController::class, 'index'])->name('tenant.orders');
    });

    // Logout (POST, no auth middleware since session is still valid)
    Route::post('logout', [TenantLoginController::class, 'destroy'])->name('tenant.logout');
});

}); // end Route::domain('{subdomain}.'.config('app.tenant_domain'))
