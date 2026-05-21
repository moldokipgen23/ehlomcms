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
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['website', 'hosting', 'maintenance', 'ecommerce', 'other'])->default('website');
            $table->text('scope')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->text('payment_terms')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->enum('status', ['draft', 'sent', 'signed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
