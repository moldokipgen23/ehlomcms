<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $skills = [
            ['name' => 'Google lead import', 'slug' => 'google-lead-import', 'category' => 'research', 'connector' => 'Google Places', 'description' => 'Import schools, restaurants, and qualified local businesses from configured Google Places lead sources.', 'approval_required' => false],
            ['name' => 'Lead research brief', 'slug' => 'lead-research-brief', 'category' => 'research', 'connector' => 'Ehlom CRM', 'description' => 'Summarise the business, contact details, current website status, and missing facts without inventing data.', 'approval_required' => false],
            ['name' => 'Opportunity scoring', 'slug' => 'opportunity-scoring', 'category' => 'sales', 'connector' => 'Ehlom CRM', 'description' => 'Score lead quality, urgency, website gap, and product fit with clear reasons.', 'approval_required' => false],
            ['name' => 'Prototype planning', 'slug' => 'prototype-planning', 'category' => 'content', 'connector' => 'Theme Catalog', 'description' => 'Match a reusable business-type demo and write a personalised prototype brief for that lead.', 'approval_required' => false],
            ['name' => 'Outreach draft', 'slug' => 'outreach-draft', 'category' => 'sales', 'connector' => 'WhatsApp Web / Email draft', 'description' => 'Prepare a human-reviewed WhatsApp or email message with the prototype link.', 'approval_required' => true],
            ['name' => 'Follow-up planner', 'slug' => 'follow-up-planner', 'category' => 'sales', 'connector' => 'Ehlom CRM', 'description' => 'Create safe follow-up drafts and next action timing after the first outreach.', 'approval_required' => true],
            ['name' => 'Client conversion handoff', 'slug' => 'client-conversion-handoff', 'category' => 'operations', 'connector' => 'Ehlom CRM', 'description' => 'When a lead agrees, prepare the client, tenant, project, invoice, and product handoff checklist.', 'approval_required' => true],
        ];

        foreach ($skills as $skill) {
            DB::table('ai_skills')->updateOrInsert(
                ['slug' => $skill['slug']],
                [
                    'name' => $skill['name'],
                    'category' => $skill['category'],
                    'connector' => $skill['connector'],
                    'description' => $skill['description'],
                    'status' => 'active',
                    'approval_required' => $skill['approval_required'],
                    'settings' => json_encode([]),
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $firstGemini = DB::table('ai_provider_credentials')->where('provider', 'gemini')->where('is_active', true)->value('id');
        $firstDeepSeek = DB::table('ai_provider_credentials')->where('provider', 'deepseek')->where('is_active', true)->value('id');
        $credentialFor = function (string $provider) use ($firstGemini, $firstDeepSeek): ?int {
            return match ($provider) {
                'deepseek' => $firstDeepSeek,
                'gemini' => $firstGemini,
                default => null,
            };
        };

        $agents = [
            ['name' => 'Ehlom Sales Orchestrator', 'slug' => 'ehlom-sales-orchestrator', 'role' => 'Runs the full supervised lead-to-prototype sales flow', 'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'skills' => ['google-lead-import', 'lead-research-brief', 'opportunity-scoring', 'prototype-planning', 'outreach-draft', 'follow-up-planner', 'client-conversion-handoff']],
            ['name' => 'Lead Finder', 'slug' => 'lead-finder', 'role' => 'Finds and deduplicates school, restaurant, and local business leads', 'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'skills' => ['google-lead-import', 'lead-research-brief']],
            ['name' => 'Research Analyst', 'slug' => 'research-analyst', 'role' => 'Builds a factual business and website audit from available lead data', 'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'skills' => ['lead-research-brief']],
            ['name' => 'Opportunity Scorer', 'slug' => 'opportunity-scorer', 'role' => 'Scores lead quality and recommends the best Ehlom product', 'provider' => 'deepseek', 'model' => 'deepseek-chat', 'skills' => ['opportunity-scoring']],
            ['name' => 'Prototype Builder', 'slug' => 'prototype-builder', 'role' => 'Creates the personalised website prototype plan using reusable demo themes', 'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'skills' => ['prototype-planning']],
            ['name' => 'Outreach Writer', 'slug' => 'outreach-writer', 'role' => 'Writes WhatsApp and email drafts with the matched prototype link', 'provider' => 'deepseek', 'model' => 'deepseek-chat', 'skills' => ['outreach-draft']],
            ['name' => 'Follow-up Planner', 'slug' => 'follow-up-planner-agent', 'role' => 'Plans follow-up timing, message copy, and conversion handoff', 'provider' => 'deepseek', 'model' => 'deepseek-chat', 'skills' => ['follow-up-planner', 'client-conversion-handoff']],
        ];

        foreach ($agents as $agent) {
            DB::table('ai_agents')->updateOrInsert(
                ['slug' => $agent['slug']],
                [
                    'tenant_id' => null,
                    'created_by' => null,
                    'provider_credential_id' => $credentialFor($agent['provider']),
                    'name' => $agent['name'],
                    'role' => $agent['role'],
                    'provider' => $agent['provider'],
                    'model' => $agent['model'],
                    'fallback_provider' => $agent['provider'] === 'deepseek' ? 'gemini' : 'deepseek',
                    'fallback_model' => $agent['provider'] === 'deepseek' ? 'gemini-2.5-flash' : 'deepseek-chat',
                    'description' => 'Part of the supervised Ehlom AI sales workforce. It prepares work for admin review and does not send external messages automatically.',
                    'status' => 'active',
                    'avatar' => 'ti-robot',
                    'settings' => json_encode(['supervised' => true, 'external_actions' => 'approval_only']),
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $agentId = DB::table('ai_agents')->where('slug', $agent['slug'])->value('id');
            $skillIds = DB::table('ai_skills')->whereIn('slug', $agent['skills'])->pluck('id');
            foreach ($skillIds as $skillId) {
                DB::table('ai_agent_skill')->updateOrInsert(
                    ['ai_agent_id' => $agentId, 'ai_skill_id' => $skillId],
                    ['enabled' => true, 'settings' => json_encode([]), 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        $orchestratorId = DB::table('ai_agents')->where('slug', 'ehlom-sales-orchestrator')->value('id');
        DB::table('ai_workflows')->updateOrInsert(
            ['slug' => 'lead-to-prototype-sales-flow'],
            [
                'tenant_id' => null,
                'ai_agent_id' => $orchestratorId,
                'created_by' => null,
                'name' => 'Lead to Prototype Sales Flow',
                'description' => 'Find context, research the lead, score opportunity, match/build a reusable prototype brief, prepare WhatsApp/email copy, and pause for human approval.',
                'trigger_type' => 'manual',
                'status' => 'active',
                'approval_required' => true,
                'steps' => json_encode([
                    ['order' => 1, 'name' => 'Lead research brief', 'type' => 'skill', 'agent_slug' => 'research-analyst'],
                    ['order' => 2, 'name' => 'Website opportunity audit', 'type' => 'skill', 'agent_slug' => 'research-analyst'],
                    ['order' => 3, 'name' => 'Lead scoring and offer match', 'type' => 'skill', 'agent_slug' => 'opportunity-scorer'],
                    ['order' => 4, 'name' => 'Prototype brief and demo match', 'type' => 'skill', 'agent_slug' => 'prototype-builder'],
                    ['order' => 5, 'name' => 'WhatsApp outreach draft', 'type' => 'skill', 'agent_slug' => 'outreach-writer'],
                    ['order' => 6, 'name' => 'Follow-up plan', 'type' => 'skill', 'agent_slug' => 'follow-up-planner-agent'],
                    ['order' => 7, 'name' => 'Human approval', 'type' => 'approval', 'agent_slug' => 'ehlom-sales-orchestrator'],
                ]),
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('ai_workflows')
            ->where('slug', 'qualify-local-business-leads')
            ->update(['status' => 'paused', 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('ai_workflows')->where('slug', 'lead-to-prototype-sales-flow')->delete();
        DB::table('ai_agents')->whereIn('slug', [
            'ehlom-sales-orchestrator',
            'lead-finder',
            'research-analyst',
            'opportunity-scorer',
            'prototype-builder',
            'outreach-writer',
            'follow-up-planner-agent',
        ])->delete();
    }
};
