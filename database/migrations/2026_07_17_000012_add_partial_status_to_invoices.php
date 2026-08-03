<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'unpaid', 'partial', 'paid', 'overdue') NOT NULL DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE invoices SET status = 'unpaid' WHERE status = 'partial'");
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'unpaid', 'paid', 'overdue') NOT NULL DEFAULT 'unpaid'");
    }
};
