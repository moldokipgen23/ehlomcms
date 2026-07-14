<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The standalone `hosting_plans` table (added earlier this session to
     * link a plan to a tenant) turned out to duplicate the pre-existing
     * `products` table (category=hosting), which already has real client
     * billing history (client_product, project_product, subscriptions rows
     * reference it) - two "hosting plan" lists showing the same prices with
     * no relationship between them. Product is the one with real data
     * behind it, so tenants.hosting_plan_id now points there instead.
     * hosting_plans had zero tenants assigned when this ran, so there is
     * nothing to migrate - just repoint the column and drop the table.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['hosting_plan_id']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('hosting_plan_id')->references('id')->on('products')->nullOnDelete();
        });

        Schema::dropIfExists('hosting_plans');
    }

    public function down(): void
    {
        Schema::create('hosting_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('provider')->nullable();
            $table->json('features')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['hosting_plan_id']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('hosting_plan_id')->references('id')->on('hosting_plans')->nullOnDelete();
        });
    }
};
