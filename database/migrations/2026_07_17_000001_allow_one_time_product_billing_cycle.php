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

        DB::statement("ALTER TABLE products MODIFY billing_cycle ENUM('one_time','monthly','quarterly','yearly') NOT NULL DEFAULT 'one_time'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE products SET billing_cycle = 'yearly' WHERE billing_cycle = 'one_time'");
        DB::statement("ALTER TABLE products MODIFY billing_cycle ENUM('monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly'");
    }
};
