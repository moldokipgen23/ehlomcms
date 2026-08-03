<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ai_agent_runs', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('tenant_id')->constrained('leads')->nullOnDelete();
            $table->string('approval_state')->default('not_required')->after('status');
            $table->index(['lead_id', 'status']);
        });

        Schema::create('ai_workflow_run_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_agent_run_id')->constrained('ai_agent_runs')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('name');
            $table->string('type')->default('skill');
            $table->string('status')->default('queued');
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->unique(['ai_agent_run_id', 'step_order']);
        });

        Schema::create('ai_prototypes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('ai_agent_run_id')->nullable()->constrained('ai_agent_runs')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('business_type')->nullable();
            $table->string('status')->default('draft');
            $table->string('preview_url')->nullable();
            $table->json('content')->nullable();
            $table->json('assets')->nullable();
            $table->timestamps();
            $table->index(['lead_id', 'status']);
        });

        // The default lead workflow must demonstrate the prototype checkpoint.
        $workflow = DB::table('ai_workflows')->where('slug', 'qualify-local-business-leads')->first();
        if ($workflow) {
            $steps = json_decode($workflow->steps ?: '[]', true) ?: [];
            $hasPrototype = collect($steps)->contains(fn (array $step): bool => str_contains(strtolower($step['name'] ?? ''), 'prototype'));
            if (!$hasPrototype) {
                $steps = collect($steps)
                    ->map(function (array $step): array {
                        if (($step['order'] ?? 0) >= 5) {
                            $step['order']++;
                        }
                        return $step;
                    })
                    ->push(['order' => 5, 'name' => 'Prototype brief', 'type' => 'skill'])
                    ->sortBy('order')
                    ->values()
                    ->all();
                DB::table('ai_workflows')->where('id', $workflow->id)->update(['steps' => json_encode($steps), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_prototypes');
        Schema::dropIfExists('ai_workflow_run_steps');
        Schema::table('ai_agent_runs', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropIndex(['lead_id', 'status']);
            $table->dropColumn(['lead_id', 'approval_state']);
        });
    }
};
