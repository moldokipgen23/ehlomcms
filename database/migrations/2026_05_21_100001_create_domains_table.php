<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('domain_name');
            $table->string('registrar')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('expiry_date');
            $table->decimal('renewal_cost', 10, 2)->nullable();
            $table->string('hosting_server')->nullable();
            $table->string('hosting_plan')->nullable();
            $table->string('nameserver')->nullable();
            $table->text('cloudpanel_notes')->nullable();
            $table->enum('status', ['active', 'expired', 'transferred'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
