<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_type_modules', function (Blueprint $table) {
            // A row's existence still means "assigned" to this business type;
            // status distinguishes whether it's bundled free or sold as a
            // paid add-on for that type. Existing rows all default to
            // 'free', matching what row-existence alone used to mean.
            $table->enum('status', ['free', 'paid'])->default('free')->after('module_key');
            $table->decimal('price', 10, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('business_type_modules', function (Blueprint $table) {
            $table->dropColumn(['status', 'price']);
        });
    }
};
