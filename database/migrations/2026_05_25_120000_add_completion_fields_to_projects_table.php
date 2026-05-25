<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('completion_summary')->nullable()->after('notes');
            $table->text('upsell_notes')->nullable()->after('completion_summary');
            $table->string('deliverable_pdf')->nullable()->after('upsell_notes');
            $table->timestamp('completed_at')->nullable()->after('deliverable_pdf');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['completion_summary', 'upsell_notes', 'deliverable_pdf', 'completed_at']);
        });
    }
};
