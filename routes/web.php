<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminAddonProductController;
use App\Http\Controllers\AdminBackupController;
use App\Http\Controllers\AdminDomainController;
use App\Http\Controllers\AdminImpersonateController;
use App\Http\Controllers\AdminTenantAddonController;
use App\Http\Controllers\AdminTenantController;
use App\Http\Controllers\AdminAiContentController;
use App\Http\Controllers\AdminAiSettingsController;
use App\Http\Controllers\AdminAuditLogController;
use App\Http\Controllers\AdminHostingController;
use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\AdminRevenueController;
use App\Http\Controllers\AdminSystemHealthController;
use App\Http\Controllers\AdminEmailTemplateController;
use App\Http\Controllers\AdminExpenseController;
use App\Http\Controllers\AdminMediaLibraryController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AdminThemeController;
use App\Http\Controllers\AdminUserController;
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
    Route::get('revenue', [AdminRevenueController::class, 'index'])->name('revenue.index');

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
    Route::post('tenants/{tenant}/hosting-plan', [AdminTenantController::class, 'updateHostingPlan'])->name('tenants.hosting-plan');

    Route::get('business-modules', [AdminModuleController::class, 'index'])->name('modules.index');
    Route::post('business-modules/{businessType}', [AdminModuleController::class, 'updateAssignments'])->name('modules.update-assignments');

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
    Route::get('system-health', [AdminSystemHealthController::class, 'index'])->name('system-health.index');
    Route::post('system-health/clear-cache', [AdminSystemHealthController::class, 'clearCache'])->name('system-health.clear-cache');
    Route::post('system-health/migrate', [AdminSystemHealthController::class, 'runMigration'])->name('system-health.migrate');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/test-email', [SettingController::class, 'testEmail'])->name('settings.testEmail');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Wave 3 — Users & Roles. Gated admin-only: this is the screen that
    // grants/revokes admin access itself, so staff (if any exist) must not
    // be able to create themselves another admin account.
    Route::resource('users', AdminUserController::class)->except('show')->middleware('admin.role:admin');

    // Wave 3 — Impersonation. Admin-only: lets whoever holds it log into
    // ANY tenant's dashboard without a password.
    Route::post('tenants/{tenant}/impersonate', [AdminImpersonateController::class, 'loginAsTenant'])
        ->middleware('admin.role:admin')
        ->name('tenants.impersonate');

    // Wave 3 — Support Tickets (admin)
    Route::get('tickets', [AdminTicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('tickets/{ticket}', [AdminTicketController::class, 'show'])->name('admin.tickets.show');
    Route::post('tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('admin.tickets.reply');
    Route::post('tickets/{ticket}/close', [AdminTicketController::class, 'close'])->name('admin.tickets.close');

    // Wave 3 — Custom Domains. Named domains.admin.* (not domains.store etc.)
    // to avoid colliding with the pre-existing Route::resource('domains', ...)
    // above, which is the unrelated agency CRM hosting-records feature.
    Route::get('domains', [AdminDomainController::class, 'index'])->name('domains.admin.index');
    Route::post('domains/{tenant}/set', [AdminDomainController::class, 'store'])->name('domains.admin.store');
    Route::post('domains/{tenant}/verify', [AdminDomainController::class, 'verify'])->name('domains.verify');
    // Admin-only: these run real shell commands (certbot) and DNS-facing
    // actions against the live server.
    Route::post('domains/{tenant}/remove', [AdminDomainController::class, 'remove'])->middleware('admin.role:admin')->name('domains.remove');
    Route::post('domains/{tenant}/ssl-issue', [AdminDomainController::class, 'issueSsl'])->middleware('admin.role:admin')->name('domains.ssl-issue');
    Route::post('domains/{tenant}/ssl-renew', [AdminDomainController::class, 'renewSsl'])->middleware('admin.role:admin')->name('domains.ssl-renew');

    // Wave 3 — Backups
    Route::get('backups', [AdminBackupController::class, 'index'])->name('backups.index');
    Route::post('backups/run', [AdminBackupController::class, 'run'])->name('backups.run');
    Route::get('backups/download/{filename}', [AdminBackupController::class, 'download'])->name('backups.download');
    // Admin-only: overwrites the live database from a backup file.
    Route::post('backups/restore', [AdminBackupController::class, 'restore'])->middleware('admin.role:admin')->name('backups.restore');
    Route::post('backups/tenant/{tenant}/backup-assets', [AdminBackupController::class, 'backupAssets'])->name('backups.tenant-assets-backup');
    Route::get('backups/tenant/{tenant}', [AdminBackupController::class, 'tenantBackups'])->name('backups.tenant-assets');
    Route::get('backups/tenant-download/{tenantBackup}', [AdminBackupController::class, 'downloadTenantBackup'])->name('backups.tenant-download');
    // Admin-only: overwrites a tenant's live storefront assets from a backup zip.
    Route::post('backups/tenant/{tenant}/restore/{tenantBackup}', [AdminBackupController::class, 'restoreAssets'])->middleware('admin.role:admin')->name('backups.tenant-restore-assets');
    Route::delete('backups/tenant-backup/{tenantBackup}', [AdminBackupController::class, 'destroyTenantBackup'])->name('backups.tenant-destroy');

    // Wave 4.1 — Media Library
    Route::get('media', [AdminMediaLibraryController::class, 'index'])->name('media.index');

    // Wave 4.2 — Email Templates
    Route::resource('email-templates', AdminEmailTemplateController::class)->except('show');

    // Wave 4.3 — Payments
    Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [AdminPaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [AdminPaymentController::class, 'store'])->name('payments.store');
    Route::delete('payments/{payment}', [AdminPaymentController::class, 'destroy'])->name('payments.destroy');

    // Wave 4.4 — Expenses
    Route::get('expenses', [AdminExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/create', [AdminExpenseController::class, 'create'])->name('expenses.create');
    Route::post('expenses', [AdminExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('expenses/{expense}', [AdminExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Wave 4.5 — Theme SDK download (theme.zip export)
    Route::get('themes/{theme}/download', [AdminThemeController::class, 'downloadAsZip'])->name('themes.download');

    // Audit Logs
    Route::get('audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');

    // Hosting Plans
    Route::get('hosting', [AdminHostingController::class, 'index'])->name('hosting.index');
    Route::post('hosting/plans', [AdminHostingController::class, 'storePlan'])->name('hosting.plans.store');
    Route::delete('hosting/plans/{hostingPlan}', [AdminHostingController::class, 'destroyPlan'])->name('hosting.plans.destroy');

    // AI Settings
    Route::get('ai-settings', [AdminAiSettingsController::class, 'index'])->name('ai-settings.index');
    Route::post('ai-settings/{tenant}', [AdminAiSettingsController::class, 'update'])->name('ai-settings.update');

    // AI Content
    Route::get('ai-content', [AdminAiContentController::class, 'index'])->name('ai-content.index');
    Route::post('ai-content/generate', [AdminAiContentController::class, 'generate'])->name('ai-content.generate');
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
