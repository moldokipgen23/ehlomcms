<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
            // Widen from enum so 'one_time' billing is allowed.
            $table->string('billing_cycle')->default('yearly')->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('type')->nullable(false)->change();
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly')->change();
        });
    }
};
