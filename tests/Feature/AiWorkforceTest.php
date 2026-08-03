<?php

namespace Tests\Feature;

use App\Models\AiAgent;
use App\Models\AiSkill;
use App\Models\AiWorkflow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiWorkforceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_open_agent_studio(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('ai-agents.index'))
            ->assertOk()
            ->assertSee('AI Workforce')
            ->assertSee('Ehlom Lead Scout');
    }

    public function test_agent_can_be_created_with_multiple_reusable_skills(): void
    {
        $user = User::factory()->create();
        $skills = AiSkill::query()->take(2)->pluck('id')->all();

        $this->actingAs($user)
            ->post(route('ai-agents.store'), [
                'name' => 'Sales Assistant',
                'role' => 'Sales qualification',
                'description' => 'Qualifies approved leads.',
                'status' => 'draft',
                'skills' => $skills,
            ])
            ->assertRedirect(route('ai-agents.index'));

        $agent = AiAgent::where('name', 'Sales Assistant')->firstOrFail();
        $this->assertCount(2, $agent->skills);
    }

    public function test_workflow_turns_each_line_into_an_ordered_step(): void
    {
        $user = User::factory()->create();
        $agent = AiAgent::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('ai-workflows.store'), [
                'name' => 'Lead qualification',
                'ai_agent_id' => $agent->id,
                'trigger_type' => 'manual',
                'status' => 'draft',
                'steps_text' => "Find leads\nScore leads\nRequest approval",
                'approval_required' => '1',
            ])
            ->assertRedirect(route('ai-workflows.index'));

        $workflow = AiWorkflow::where('name', 'Lead qualification')->firstOrFail();
        $this->assertSame(['Find leads', 'Score leads', 'Request approval'], collect($workflow->steps)->pluck('name')->all());
    }
}
