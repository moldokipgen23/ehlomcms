<?php

namespace App\Http\Controllers;

use App\Models\AiAgent;
use App\Models\AiAgentRun;
use App\Models\AiWorkflow;
use App\Models\AiProviderCredential;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Services\AiWorkflowRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAiWorkflowController extends Controller
{
    public function index(): View
    {
        $workflows = AiWorkflow::with(['agent.providerCredential', 'tenant'])->latest()->paginate(20);
        $leads = Lead::latest()->limit(100)->get();
        $runs = AiAgentRun::with(['workflow', 'lead'])->latest()->limit(10)->get();
        $salesAgentSlugs = [
            'ehlom-sales-orchestrator',
            'research-analyst',
            'opportunity-scorer',
            'prototype-builder',
            'outreach-writer',
            'follow-up-planner-agent',
        ];
        $salesAgents = AiAgent::with('providerCredential')->whereIn('slug', $salesAgentSlugs)->get();
        $missingAgents = collect($salesAgentSlugs)
            ->reject(fn (string $slug): bool => (bool) $salesAgents->firstWhere('slug', $slug)?->providerCredential?->is_active)
            ->values();
        $readiness = [
            'provider' => AiProviderCredential::where('is_active', true)->exists(),
            'sales_team' => $missingAgents->isEmpty(),
            'missing_agents' => $missingAgents,
            'lead' => Lead::exists(),
            'prototype' => \App\Models\AiPrototypeCatalog::active()->whereNotNull('preview_url')->exists(),
        ];
        return view('ai-agents.workflows', compact('workflows', 'leads', 'runs', 'readiness'));
    }

    public function create(): View
    {
        return view('ai-agents.workflow-create', [
            'agents' => AiAgent::whereIn('status', ['active', 'draft'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'ai_agent_id' => 'required|exists:ai_agents,id',
            'trigger_type' => 'required|in:manual,schedule,lead_created,webhook',
            'status' => 'required|in:draft,active,paused',
            'steps_text' => 'required|string|max:4000',
            'approval_required' => 'boolean',
        ]);

        $steps = collect(preg_split('/\r\n|\r|\n/', $validated['steps_text']))
            ->map(fn ($step) => trim($step))
            ->filter()
            ->values()
            ->map(fn ($step, $index) => [
                'order' => $index + 1,
                'name' => $step,
                'type' => 'skill',
            ])->all();

        $workflow = AiWorkflow::create([
            'tenant_id' => null,
            'ai_agent_id' => $validated['ai_agent_id'],
            'description' => $validated['description'] ?? null,
            'name' => $validated['name'],
            'trigger_type' => $validated['trigger_type'],
            'status' => $validated['status'],
            'slug' => Str::slug($validated['name']) . '-' . Str::lower(Str::random(6)),
            'created_by' => $request->user()->id,
            'approval_required' => $request->boolean('approval_required'),
            'steps' => $steps,
        ]);

        AuditLog::log('ai_workflow_created', "AI workflow {$workflow->name} created", 'ai_workflow', $workflow->id);

        return redirect()->route('ai-workflows.index')->with('success', 'Workflow saved. Select a lead to run it under supervision.');
    }

    public function run(Request $request, AiWorkflow $workflow, AiWorkflowRunner $runner): RedirectResponse
    {
        $validated = $request->validate(['lead_id' => 'required|exists:leads,id']);
        try {
            $run = $runner->start($workflow, Lead::findOrFail($validated['lead_id']), $request->user());
            $run = $runner->process($run);
        } catch (\Throwable $error) {
            return back()->withErrors(['workflow' => $error->getMessage()]);
        }

        return redirect()->route('ai-runs.show', $run)->with('success', $this->runMessage($run->status));
    }

    public function showRun(AiAgentRun $run): View
    {
        $run->load(['agent', 'workflow', 'lead', 'steps', 'prototype']);
        return view('ai-agents.run', compact('run'));
    }

    public function approveRun(Request $request, AiAgentRun $run, AiWorkflowRunner $runner): RedirectResponse
    {
        try {
            $runner->approve($run, $request->user());
        } catch (\Throwable $error) {
            return back()->withErrors(['approval' => $error->getMessage()]);
        }

        return back()->with('success', 'Draft approved. No WhatsApp or email was sent; an external sender must be connected separately.');
    }

    private function runMessage(string $status): string
    {
        return match ($status) {
            'awaiting_approval' => 'Run paused for your approval before any external action.',
            'failed' => 'Run stopped with an error. Review the run details.',
            default => 'Workflow run completed.',
        };
    }
}
