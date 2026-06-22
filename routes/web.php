<?php

use App\Http\Controllers\ActivityController;
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

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/get-quote', [LeadController::class, 'create'])->name('leads.create');
Route::post('/get-quote', [LeadController::class, 'store'])->name('leads.store');
Route::get('/thank-you', [LeadController::class, 'thankyou'])->name('leads.thankyou');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

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

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/test-email', [SettingController::class, 'testEmail'])->name('settings.testEmail');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
