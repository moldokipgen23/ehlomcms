<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->default('custom')->after('name');
        });

        // Backfill category from the existing free-text type.
        DB::table('products')->where('type', 'Domain Registration')->update(['category' => 'domain']);
        DB::table('products')->where('type', 'Website Hosting')->update(['category' => 'hosting']);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
