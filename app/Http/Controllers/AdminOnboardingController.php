<?php

namespace App\Http\Controllers;

use App\Models\BusinessTypeModule;
use App\Models\Tenant;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOnboardingController extends Controller
{
    private const STEPS = ['info', 'theme', 'modules', 'domain', 'done'];

    public function show(Tenant $tenant, string $step): View|RedirectResponse
    {
        if (!in_array($step, self::STEPS)) {
            abort(404);
        }

        if ($step === 'done' && $tenant->onboarding_step === 'done') {
            return redirect()->route('tenants.index');
        }

        $themes = Theme::orderBy('name')->get()->keyBy('key');
        $modules = config('modules');
        $businessTypes = config('business_types');

        $freeByType = [];
        foreach ($businessTypes as $typeKey => $type) {
            $freeByType[$typeKey] = BusinessTypeModule::modulesFor($typeKey);
        }

        $client = $tenant->client;

        return view("onboarding.step-" . array_search($step, self::STEPS) . "-{$step}", compact(
            'tenant', 'themes', 'modules', 'businessTypes', 'freeByType', 'client', 'step'
        ));
    }

    public function update(Request $request, Tenant $tenant, string $step): RedirectResponse
    {
        if (!in_array($step, self::STEPS)) {
            abort(404);
        }

        switch ($step) {
            case 'info':
                $data = $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'contact_email' => ['nullable', 'email', 'max:255'],
                    'contact_phone' => ['nullable', 'string', 'max:30'],
                    'whatsapp_number' => ['nullable', 'string', 'max:30'],
                    'about_text' => ['nullable', 'string'],
                    'contact_address' => ['nullable', 'string', 'max:500'],
                    'contact_hours' => ['nullable', 'string', 'max:255'],
                ]);
                $tenant->update($data);
                $nextStep = 'theme';
                break;

            case 'theme':
                $data = $request->validate([
                    'template_id' => ['required', 'string'],
                ]);
                $tenant->update($data);
                $nextStep = 'modules';
                break;

            case 'modules':
                $data = $request->validate([
                    'modules' => ['nullable', 'array'],
                    'modules.*' => ['string'],
                ]);
                $tenant->update(['modules' => $data['modules'] ?? []]);
                $nextStep = 'domain';
                break;

            case 'domain':
                $data = $request->validate([
                    'custom_domain' => ['nullable', 'string', 'max:255'],
                ]);

                if (!empty($data['custom_domain'])) {
                    $domain = strtolower(trim($data['custom_domain']));
                    $tenant->update([
                        'custom_domain' => $domain,
                        'domain_status' => 'pending',
                    ]);
                }
                $nextStep = 'done';
                break;

            case 'done':
                $tenant->update(['onboarding_step' => 'done']);
                return redirect()->route('tenants.index')->with('success', 'Onboarding complete!');

            default:
                abort(404);
        }

        $tenant->update(['onboarding_step' => $nextStep]);

        return redirect()->route('onboarding.step', ['tenant' => $tenant, 'step' => $nextStep]);
    }

    public function skip(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['onboarding_step' => 'done']);

        return redirect()->route('tenants.index')->with('success', 'Onboarding skipped. You can edit tenant settings anytime.');
    }
}
