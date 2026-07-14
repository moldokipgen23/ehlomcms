<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('project_type')->nullable()->after('status');
            $table->decimal('budget_min', 12, 2)->nullable()->after('project_type');
            $table->decimal('budget_max', 12, 2)->nullable()->after('budget_min');
            $table->string('timeline')->nullable()->after('budget_max');
            $table->string('source')->nullable()->after('timeline');
            $table->text('features')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'project_type',
                'budget_min',
                'budget_max',
                'timeline',
                'source',
                'features',
            ]);
        });
    }
};
