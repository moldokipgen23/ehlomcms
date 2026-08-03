<?php

namespace App\Services;

use App\Models\AiAgentRun;
use App\Models\AiPrototype;
use App\Models\AiAgent;
use App\Models\AiWorkflow;
use App\Models\AiWorkflowRunStep;
use App\Models\ExternalCatalogProduct;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class AiWorkflowRunner
{
    public function __construct(
        private readonly AiProviderGateway $gateway,
        private readonly PrototypeMatcher $prototypeMatcher,
    )
    {
    }

    public function start(AiWorkflow $workflow, Lead $lead, User $user): AiAgentRun
    {
        $workflow->load(['agent.providerCredential', 'agent.skills']);
        if (!$workflow->agent) {
            throw new RuntimeException('This workflow has no AI agent assigned.');
        }
        if ($workflow->status === 'paused') {
            throw new RuntimeException('This workflow is paused. Activate it before running.');
        }
        if (!$workflow->steps) {
            throw new RuntimeException('This workflow has no steps configured.');
        }

        return DB::transaction(function () use ($workflow, $lead, $user): AiAgentRun {
            $prototype = $this->prototypeMatcher->assign($lead);
            $run = AiAgentRun::create([
                'ai_agent_id' => $workflow->ai_agent_id,
                'ai_workflow_id' => $workflow->id,
                'tenant_id' => $workflow->tenant_id,
                'lead_id' => $lead->id,
                'status' => 'queued',
                'approval_state' => $workflow->approval_required ? 'required' : 'not_required',
                'trigger' => 'manual',
                'input' => [
                    'lead_id' => $lead->id,
                    'lead' => $this->leadContext($lead),
                    'matched_prototype' => $prototype,
                    'started_by' => $user->id,
                ],
            ]);

            foreach (collect($workflow->steps)->sortBy('order') as $step) {
                AiWorkflowRunStep::create([
                    'ai_agent_run_id' => $run->id,
                    'step_order' => (int) ($step['order'] ?? 0),
                    'name' => trim((string) ($step['name'] ?? 'Unnamed step')),
                    'type' => $step['type'] ?? 'skill',
                    'status' => 'queued',
                    'input' => ['lead_id' => $lead->id],
                ]);
            }

            return $run;
        });
    }

    public function process(AiAgentRun $run): AiAgentRun
    {
        $run->load(['agent.providerCredential', 'agent.skills', 'workflow', 'lead', 'steps']);
        $context = $this->leadContext($run->lead);
        $prototype = $this->prototypeMatcher->match($run->lead);
        $context['matched_prototype'] = $prototype;
        $results = [];
        $run->forceFill([
            'status' => 'running',
            'started_at' => now(),
            'error' => null,
        ])->save();

        try {
            foreach ($run->steps as $step) {
                if ($step->status === 'completed') {
                    $results[$step->step_order] = $step->output;
                    continue;
                }

                $step->forceFill([
                    'status' => 'running',
                    'started_at' => now(),
                    'input' => ['lead' => $context, 'previous_steps' => $results],
                ])->save();

                $name = strtolower($step->name);
                $requiresApproval = $step->type === 'approval'
                    || str_contains($name, 'human approval')
                    || str_contains($name, 'send')
                    || str_contains($name, 'whatsapp')
                    || str_contains($name, 'email')
                    || str_contains($name, 'follow-up')
                    || str_contains($name, 'outreach');

                if ($step->type === 'approval' || str_contains($name, 'human approval')) {
                    $this->pauseForApproval($run, $step, 'Review the generated prototype and outreach draft before any external message is sent.');
                    break;
                }

                $stepAgent = $this->agentForStep($run, $step);
                $response = $this->gateway->generate($stepAgent, $this->messagesFor($step, $context, $results), [
                    'json' => true,
                    'temperature' => 0.25,
                    'max_output_tokens' => str_contains($name, 'prototype') ? 2200 : 1400,
                ]);
                $parsed = json_decode($response['content'], true);
                $output = [
                    'agent' => $stepAgent->name,
                    'agent_slug' => $stepAgent->slug,
                    'provider' => $response['provider'],
                    'model' => $response['model'],
                    'content' => is_array($parsed) ? $parsed : $response['content'],
                ];
                if (str_contains($name, 'message') || str_contains($name, 'outreach') || str_contains($name, 'follow')) {
                    $output = $this->attachPrototypeToMessage($output, $prototype);
                }
                if (str_contains($name, 'scoring')) {
                    $this->persistQualification($run->lead, $output['content']);
                }
                $results[$step->step_order] = $output;

                $step->forceFill([
                    'status' => $requiresApproval ? 'awaiting_approval' : 'completed',
                    'output' => $output,
                    'finished_at' => now(),
                ])->save();

                if (str_contains($name, 'prototype')) {
                    $prototype = AiPrototype::updateOrCreate(
                        ['ai_agent_run_id' => $run->id],
                        [
                            'lead_id' => $run->lead_id,
                            'tenant_id' => $run->tenant_id,
                            'name' => ($run->lead?->business_name ?: $run->lead?->name ?: 'Lead') . ' prototype',
                            'business_type' => $run->lead?->project_type,
                            'status' => 'draft',
                            'preview_url' => $prototype['preview_url'],
                            'content' => [
                                'brief' => $output['content'],
                                'matched_prototype' => $prototype,
                                'source_step' => $step->name,
                                'generated_at' => now()->toIso8601String(),
                            ],
                            'assets' => [],
                        ]
                    );
                    $results[$step->step_order]['prototype_id'] = $prototype->id;
                    $step->forceFill(['output' => $results[$step->step_order]])->save();
                }

                if ($requiresApproval) {
                    $this->pauseForApproval($run, $step, 'This draft is ready. Approve it before connecting an external sender.');
                    break;
                }
            }

            $run->refresh();
            if ($run->status === 'running') {
                $run->forceFill([
                    'status' => 'completed',
                    'approval_state' => $run->workflow?->approval_required ? 'approved' : 'not_required',
                    'output' => ['steps' => $results, 'external_actions' => 'none'],
                    'finished_at' => now(),
                ])->save();
            } else {
                $run->forceFill(['output' => ['steps' => $results, 'external_actions' => 'blocked_until_approval']])->save();
            }
        } catch (Throwable $error) {
            $run->forceFill([
                'status' => 'failed',
                'error' => $error->getMessage(),
                'output' => ['steps' => $results],
                'finished_at' => now(),
            ])->save();
            $current = $run->steps()->where('status', 'running')->first();
            $current?->forceFill(['status' => 'failed', 'error' => $error->getMessage(), 'finished_at' => now()])->save();
        }

        return $run->fresh(['agent', 'workflow', 'lead', 'steps', 'prototype']);
    }

    public function approve(AiAgentRun $run, User $user): AiAgentRun
    {
        $run->load('steps');
        $step = $run->steps()->where('status', 'awaiting_approval')->first();
        if (!$step) {
            throw new RuntimeException('There is no pending approval on this run.');
        }

        $step->forceFill([
            'status' => 'completed',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'finished_at' => now(),
        ])->save();
        $run->forceFill([
            'status' => 'completed',
            'approval_state' => 'approved',
            'output' => array_merge($run->output ?: [], [
                'approval' => ['approved_by' => $user->id, 'approved_at' => now()->toIso8601String()],
                'external_actions' => 'not_sent_connector_required',
            ]),
            'finished_at' => now(),
        ])->save();

        return $run->fresh(['agent', 'workflow', 'lead', 'steps', 'prototype']);
    }

    private function pauseForApproval(AiAgentRun $run, AiWorkflowRunStep $step, string $reason): void
    {
        $step->forceFill(['status' => 'awaiting_approval', 'error' => $reason])->save();
        $run->forceFill([
            'status' => 'awaiting_approval',
            'approval_state' => 'awaiting',
            'output' => array_merge($run->output ?: [], ['approval_required' => $reason]),
        ])->save();
    }

    private function messagesFor(AiWorkflowRunStep $step, array $lead, array $results): array
    {
        $name = strtolower($step->name);
        $goal = match (true) {
            str_contains($name, 'discovery') => 'Create a clean internal discovery record and identify missing facts. Do not invent facts.',
            str_contains($name, 'research') => 'Create a factual business research brief from supplied lead data: business type, location, contact channels, current website status, useful notes, and unknown fields.',
            str_contains($name, 'enrichment') => 'Suggest permitted enrichment fields and mark every unknown as unknown. Do not claim an external API lookup occurred.',
            str_contains($name, 'audit') => 'Assess the public website opportunity using only the supplied lead facts. Return signals, gaps, and recommended offer.',
            str_contains($name, 'scoring') || str_contains($name, 'score') => 'Score this lead from 0 to 100 with transparent reasons and recommend the best matching available offer. Use only the supplied offer catalog.',
            str_contains($name, 'prototype') => 'Create a structured website prototype brief for this business using the matched reusable demo as the base: page structure, hero copy, sections, CTA, visual direction, sample content, and required assets.',
            str_contains($name, 'message') || str_contains($name, 'outreach') || str_contains($name, 'whatsapp') => 'Draft a short personalised sales message with a subject, WhatsApp body, email body, channel, and one follow-up. Include the matched prototype link. Never send it.',
            str_contains($name, 'follow') => 'Prepare a compliant follow-up schedule and draft messages only. Never send anything.',
            default => 'Summarise the next useful internal action for this lead.',
        };

        return [
            ['role' => 'system', 'content' => 'You are an Ehlom supervised sales and prototype agent. Return valid JSON only. Never invent contact details, never claim a website was scraped when it was not, and never send messages or make purchases.'],
            ['role' => 'user', 'content' => json_encode([
                'task' => $goal,
                'lead' => $lead,
                'available_offers' => $this->availableOffers(),
                'previous_steps' => Arr::only($results, array_keys($results)),
                'matched_prototype' => $lead['matched_prototype'] ?? null,
                'ehlom_sales_whatsapp' => '918402831826',
                'prototype_rules' => [
                    'reuse_existing_business_type_demo' => true,
                    'do_not_create_a_public_site_without_human_approval' => true,
                    'buy_now_cta_target' => 'https://wa.me/918402831826',
                ],
                'required_json' => 'Use concise keys suited to an admin review screen.',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)],
        ];
    }

    private function agentForStep(AiAgentRun $run, AiWorkflowRunStep $step): AiAgent
    {
        $definition = collect($run->workflow?->steps ?? [])->first(function (array $item) use ($step): bool {
            return (int) ($item['order'] ?? 0) === (int) $step->step_order
                || trim((string) ($item['name'] ?? '')) === trim((string) $step->name);
        });
        $slug = is_array($definition) ? ($definition['agent_slug'] ?? null) : null;
        $agent = $slug
            ? AiAgent::with('providerCredential')->where('slug', $slug)->whereIn('status', ['active', 'draft'])->first()
            : null;

        if ($agent && $agent->providerCredential?->is_active) {
            return $agent;
        }

        if ($run->agent?->providerCredential?->is_active) {
            return $run->agent;
        }

        if ($agent) {
            return $agent;
        }

        return $run->agent;
    }

    private function availableOffers(): array
    {
        $internal = Product::query()
            ->where('status', 'active')
            ->get(['id', 'name', 'description', 'category', 'billing_cycle', 'price', 'currency'])
            ->map(fn (Product $product) => [
                'source' => 'ehlom',
                'id' => (string) $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category,
                'billing_cycle' => $product->billing_cycle,
                'price' => $product->price,
                'currency' => $product->currency ?: 'INR',
            ]);

        $external = ExternalCatalogProduct::query()
            ->where('status', 'active')
            ->with('integration:id,name,driver')
            ->get()
            ->map(fn (ExternalCatalogProduct $product) => [
                'source' => $product->integration?->name ?: 'external_erp',
                'source_driver' => $product->integration?->driver,
                'id' => (string) $product->external_id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category,
                'billing_cycle' => $product->billing_cycle,
                'price' => $product->price,
                'currency' => $product->currency ?: 'INR',
            ]);

        return $internal->concat($external)->values()->all();
    }

    private function leadContext(?Lead $lead): array
    {
        if (!$lead) {
            throw new RuntimeException('This run has no lead context.');
        }

        return Arr::only($lead->toArray(), [
            'id', 'name', 'email', 'phone', 'business_name', 'project_type',
            'description', 'features', 'budget_min', 'budget_max', 'timeline',
            'source', 'status', 'notes',
            'lead_score', 'score_reasons', 'recommended_offer', 'prototype_key', 'prototype_url',
        ]);
    }

    private function attachPrototypeToMessage(array $output, array $prototype): array
    {
        $content = $output['content'] ?? null;
        $url = $prototype['preview_url'] ?? null;

        if (is_array($content)) {
            $content['prototype_key'] = $prototype['key'] ?? null;
            $content['prototype_name'] = $prototype['label'] ?? null;
            $content['demo_url'] = $url;
            foreach (['body', 'message', 'text', 'copy'] as $key) {
                if (filled($content[$key] ?? null) && $url && !str_contains((string) $content[$key], $url)) {
                    $content[$key] = trim((string) $content[$key]) . "\n\nPreview the relevant demo: {$url}";
                    break;
                }
            }
        } elseif ($url) {
            $content = trim((string) $content) . "\n\nPreview the relevant demo: {$url}";
        }

        $output['content'] = $content;
        $output['prototype'] = $prototype;

        return $output;
    }

    private function persistQualification(?Lead $lead, mixed $content): void
    {
        if (!$lead || !is_array($content)) {
            return;
        }

        $score = data_get($content, 'score', data_get($content, 'lead_score'));
        $reasons = data_get($content, 'reasons', data_get($content, 'reasoning'));
        $offer = data_get($content, 'recommended_offer', data_get($content, 'offer'));

        $lead->forceFill([
            'lead_score' => is_numeric($score) ? max(0, min(100, (int) $score)) : $lead->lead_score,
            'score_reasons' => $reasons !== null ? (array) $reasons : $lead->score_reasons,
            'recommended_offer' => filled($offer) ? (string) $offer : ($lead->recommended_offer ?: null),
        ])->save();
    }
}
