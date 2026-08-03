<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('role')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->string('avatar')->default('ti-sparkles');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('ai_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('general');
            $table->string('connector')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->boolean('approval_required')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_agent_skill', function (Blueprint $table) {
            $table->foreignId('ai_agent_id')->constrained('ai_agents')->cascadeOnDelete();
            $table->foreignId('ai_skill_id')->constrained('ai_skills')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->primary(['ai_agent_id', 'ai_skill_id']);
        });

        Schema::create('ai_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('trigger_type')->default('manual');
            $table->string('status')->default('draft');
            $table->boolean('approval_required')->default(true);
            $table->json('steps')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('ai_agent_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_agent_id')->constrained('ai_agents')->cascadeOnDelete();
            $table->foreignId('ai_workflow_id')->nullable()->constrained('ai_workflows')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('queued');
            $table->string('trigger')->default('manual');
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['ai_agent_id', 'status']);
        });

        $now = now();
        DB::table('ai_skills')->insert([
            ['name' => 'Business discovery', 'slug' => 'business-discovery', 'category' => 'research', 'connector' => 'Hola API', 'description' => 'Find business records from the approved Hola directory source.', 'status' => 'active', 'approval_required' => false, 'settings' => json_encode(['source' => 'hola']), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Places enrichment', 'slug' => 'places-enrichment', 'category' => 'research', 'connector' => 'Google Places', 'description' => 'Enrich a lead with permitted place details and a stable place ID.', 'status' => 'active', 'approval_required' => false, 'settings' => json_encode(['source' => 'google_places']), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Website audit', 'slug' => 'website-audit', 'category' => 'research', 'connector' => 'Web research', 'description' => 'Review a public business website for fit, missing features, and opportunity signals.', 'status' => 'active', 'approval_required' => false, 'settings' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Lead scoring', 'slug' => 'lead-scoring', 'category' => 'sales', 'connector' => 'Ehlom CRM', 'description' => 'Score fit, urgency, and likely Ehlom product match.', 'status' => 'active', 'approval_required' => false, 'settings' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sales message drafting', 'slug' => 'sales-message-drafting', 'category' => 'sales', 'connector' => 'Ehlom CRM', 'description' => 'Draft a personalised outreach message without sending it.', 'status' => 'active', 'approval_required' => true, 'settings' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CRM lead sync', 'slug' => 'crm-lead-sync', 'category' => 'operations', 'connector' => 'Ehlom CRM', 'description' => 'Create or update a deduplicated lead record with source metadata.', 'status' => 'active', 'approval_required' => false, 'settings' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'WhatsApp follow-up', 'slug' => 'whatsapp-follow-up', 'category' => 'sales', 'connector' => 'WhatsApp API', 'description' => 'Prepare and send an approved follow-up message through the configured provider.', 'status' => 'active', 'approval_required' => true, 'settings' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
        ]);

        $agentId = DB::table('ai_agents')->insertGetId([
            'name' => 'Ehlom Lead Scout',
            'slug' => 'ehlom-lead-scout',
            'role' => 'Lead research and qualification',
            'description' => 'Find relevant local businesses, understand their needs, and prepare a supervised next step.',
            'status' => 'draft',
            'avatar' => 'ti-sparkles',
            'settings' => json_encode(['approval_mode' => 'external_actions']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $skillIds = DB::table('ai_skills')->whereIn('slug', [
            'business-discovery', 'places-enrichment', 'website-audit',
            'lead-scoring', 'sales-message-drafting', 'crm-lead-sync',
        ])->pluck('id');
        foreach ($skillIds as $skillId) {
            DB::table('ai_agent_skill')->insert([
                'ai_agent_id' => $agentId,
                'ai_skill_id' => $skillId,
                'enabled' => true,
                'settings' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('ai_workflows')->insert([
            'ai_agent_id' => $agentId,
            'name' => 'Qualify local business leads',
            'slug' => 'qualify-local-business-leads',
            'description' => 'Research, score, and prepare an approved outreach draft.',
            'trigger_type' => 'manual',
            'status' => 'draft',
            'approval_required' => true,
            'steps' => json_encode([
                ['order' => 1, 'name' => 'Business discovery', 'type' => 'skill'],
                ['order' => 2, 'name' => 'Places enrichment', 'type' => 'skill'],
                ['order' => 3, 'name' => 'Website audit', 'type' => 'skill'],
                ['order' => 4, 'name' => 'Lead scoring', 'type' => 'skill'],
                ['order' => 5, 'name' => 'Sales message drafting', 'type' => 'skill'],
                ['order' => 6, 'name' => 'Human approval', 'type' => 'approval'],
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agent_runs');
        Schema::dropIfExists('ai_workflows');
        Schema::dropIfExists('ai_agent_skill');
        Schema::dropIfExists('ai_skills');
        Schema::dropIfExists('ai_agents');
    }
};
