<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantMarketingSection;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantMarketingSectionController extends Controller
{
    private function tenant()
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule('marketing_sections'), 404);
        return $tenant;
    }

    public function index(): View
    {
        $tenant = $this->tenant();
        $sections = $tenant->marketingSections()->withCount('items')->get();

        return view('tenant.marketing-sections.index', compact('tenant', 'sections'));
    }

    public function create(): View
    {
        $tenant = $this->tenant();
        return view('tenant.marketing-sections.form', compact('tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenant();
        $data = $this->validated($request);
        $data['tenant_id'] = $tenant->id;
        $data['is_enabled'] = $request->boolean('is_enabled', true);
        TenantMarketingSection::create($data);

        return redirect()->route('tenant.marketing-sections')->with('success', 'Marketing section added.');
    }

    public function edit(int $id): View
    {
        $tenant = $this->tenant();
        $section = TenantMarketingSection::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('tenant.marketing-sections.form', compact('tenant', 'section'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        $section = TenantMarketingSection::where('tenant_id', $tenant->id)->findOrFail($id);
        $data = $this->validated($request);
        $data['is_enabled'] = $request->boolean('is_enabled');
        $section->update($data);

        return redirect()->route('tenant.marketing-sections')->with('success', 'Marketing section updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        TenantMarketingSection::where('tenant_id', $tenant->id)->findOrFail($id)->delete();

        return redirect()->route('tenant.marketing-sections')->with('success', 'Marketing section deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'display_style' => ['required', 'in:grid,carousel'],
            'items_per_row' => ['nullable', 'integer', 'min:1', 'max:6'],
            'filter_type' => ['nullable', 'string', 'max:50'],
            'filter_value' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_enabled' => ['nullable', 'boolean'],
        ]) + ['items_per_row' => 3, 'sort_order' => 0];
    }
}
