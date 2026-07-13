<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminAddonProductController;
use App\Http\Controllers\AdminTenantAddonController;
use App\Http\Controllers\AdminTenantController;
use App\Http\Controllers\AdminThemeController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\InfrastructureController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// These two routes share a literal URI ('/' and '/dashboard') with tenant routes
// registered in routes/tenant.php. Without a domain constraint, Laravel's route
// collection collapses same-method+same-uri routes to whichever was registered
// last, silently evicting one of them. Explicitly scoping these to the
// agency-only hosts (matching ResolveTenant's own bypass list) fixes the
// collision and ensures these never match a tenant subdomain request.
$portalHosts = 'portal\.' . preg_quote(config('app.tenant_domain', 'ehlom.com'), '/')
    . '|www\.' . preg_quote(config('app.tenant_domain', 'ehlom.com'), '/')
    . '|' . preg_quote(config('app.tenant_domain', 'ehlom.com'), '/');

Route::domain('{portalHost}')->where(['portalHost' => $portalHosts])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])->name('dashboard');
});

Route::get('/get-quote', [LeadController::class, 'create'])->name('leads.create');
Route::post('/get-quote', [LeadController::class, 'store'])->name('leads.store');
Route::get('/thank-you', [LeadController::class, 'thankyou'])->name('leads.thankyou');

Route::middleware('auth')->group(function () {
    Route::resource('clients', ClientController::class);
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/generate-invoice', [ProjectController::class, 'generateInvoice'])->name('projects.generateInvoice');
    Route::post('projects/{project}/completion', [ProjectController::class, 'saveCompletion'])->name('projects.saveCompletion');
    Route::get('projects/{project}/summary-pdf', [ProjectController::class, 'summaryPdf'])->name('projects.summaryPdf');
    Route::post('projects/{project}/send-completion-email', [ProjectController::class, 'sendCompletionEmail'])->name('projects.sendCompletionEmail');
    Route::resource('subscriptions', SubscriptionController::class)->except('show');
    Route::get('domains-hosting', [InfrastructureController::class, 'index'])->name('infrastructure.index');
    Route::resource('domains', DomainController::class)->except(['show', 'index']);
    Route::post('clients/{client}/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::patch('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
    Route::post('invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.sendEmail');
    Route::post('subscriptions/{subscription}/send-reminder', [SubscriptionController::class, 'sendReminder'])->name('subscriptions.sendReminder');
    Route::get('agreements/{agreement}/pdf', [AgreementController::class, 'downloadPdf'])->name('agreements.pdf');
    Route::resource('agreements', AgreementController::class);

    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');

    Route::get('tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('tenants/create', [AdminTenantController::class, 'create'])->name('tenants.create');
    Route::post('tenants', [AdminTenantController::class, 'store'])->name('tenants.store');
    Route::post('tenants/{tenant}/toggle-status', [AdminTenantController::class, 'toggleStatus'])->name('tenants.toggle-status');

    Route::get('themes', [AdminThemeController::class, 'index'])->name('themes.index');
    Route::get('themes/create', [AdminThemeController::class, 'create'])->name('themes.create');
    Route::post('themes', [AdminThemeController::class, 'store'])->name('themes.store');
    Route::post('themes/{theme}/toggle-public', [AdminThemeController::class, 'togglePublic'])->name('themes.toggle-public');
    Route::delete('themes/{theme}', [AdminThemeController::class, 'destroy'])->name('themes.destroy');
    Route::post('tenants/{tenant}/save-as-template', [AdminThemeController::class, 'createFromTenant'])->name('tenants.save-as-template');

    Route::post('addon-requests/{addon}/activate', [AdminTenantAddonController::class, 'activate'])->name('addon-requests.activate');
    Route::post('addon-requests/{addon}/deactivate', [AdminTenantAddonController::class, 'deactivate'])->name('addon-requests.deactivate');

    Route::get('addon-marketplace', [AdminAddonProductController::class, 'index'])->name('addon-marketplace.index');
    // Old bookmarked/cached URLs from before the addon merge.
    Route::redirect('addon-products', '/addon-marketplace');
    Route::redirect('addon-requests', '/addon-marketplace');
    Route::get('addon-products/create', [AdminAddonProductController::class, 'create'])->name('addon-products.create');
    Route::post('addon-products', [AdminAddonProductController::class, 'store'])->name('addon-products.store');
    Route::get('addon-products/{addonProduct}/edit', [AdminAddonProductController::class, 'edit'])->name('addon-products.edit');
    Route::put('addon-products/{addonProduct}', [AdminAddonProductController::class, 'update'])->name('addon-products.update');
    Route::post('addon-products/{addonProduct}/toggle-active', [AdminAddonProductController::class, 'toggleActive'])->name('addon-products.toggle-active');
    Route::delete('addon-products/{addonProduct}', [AdminAddonProductController::class, 'destroy'])->name('addon-products.destroy');

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/test-email', [SettingController::class, 'testEmail'])->name('settings.testEmail');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Razorpay webhook (no auth — signature-verified).
use App\Http\Controllers\Tenant\TenantWebhookController;
Route::post('webhook/razorpay/{subdomain}', [TenantWebhookController::class, 'handleRazorpay'])
    ->withoutMiddleware([\App\Http\Middleware\ResolveTenant::class]);

// Tenant-scoped subdomain routes.
// Only reachable when ResolveTenant middleware has resolved
// a valid, active tenant (i.e. TenantContext::check() === true).
require __DIR__.'/tenant.php';
