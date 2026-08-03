<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $invoiceBody = Setting::get('email_invoice_body');
        if ($invoiceBody !== null && ! str_contains($invoiceBody, '{payment_link}')) {
            Setting::put('email_invoice_body', rtrim($invoiceBody) . "\n\nPay securely online: {payment_link}");
        }

        $renewalBody = Setting::get('email_renewal_body');
        if ($renewalBody !== null && ! str_contains($renewalBody, '{payment_link}')) {
            Setting::put('email_renewal_body', rtrim($renewalBody) . "\n\nPay securely online: {payment_link}");
        }
    }

    public function down(): void
    {
        // Keep existing administrator-edited templates intact on rollback.
    }
};
