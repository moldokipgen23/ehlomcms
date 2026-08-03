<?php

namespace App\Http\Controllers;

use App\Models\LeadSource;
use App\Services\LeadSourceManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLeadSourceController extends Controller
{
    public function __construct(private readonly LeadSourceManager $manager) {}

    public function index(): View
    {
        return view('lead-sources.index', ['sources' => LeadSource::withCount('leads')->latest()->get()]);
    }

    public function create(): View
    {
        return view('lead-sources.form', ['source' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        LeadSource::create($this->validated($request));
        return redirect()->route('lead-sources.index')->with('success', 'Lead source added. Run a sync when it is ready.');
    }

    public function edit(LeadSource $source): View
    {
        return view('lead-sources.form', compact('source'));
    }

    public function update(Request $request, LeadSource $source): RedirectResponse
    {
        $data = $this->validated($request, $source);
        if (!array_key_exists('credentials', $data)) unset($data['credentials']);
        $source->update($data);
        return redirect()->route('lead-sources.index')->with('success', 'Lead source settings updated.');
    }

    public function sync(LeadSource $source): RedirectResponse
    {
        try {
            $result = $this->manager->sync($source);
            return back()->with('success', "Sync complete: {$result['imported']} leads imported or updated.");
        } catch (\Throwable $e) {
            $source->update(['last_synced_at' => now(), 'last_sync_status' => 'failed', 'last_error' => $e->getMessage()]);
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function destroy(LeadSource $source): RedirectResponse
    {
        $source->leads()->update(['lead_source_id' => null, 'external_id' => null, 'last_synced_at' => null]);
        $source->delete();
        return redirect()->route('lead-sources.index')->with('success', 'Lead source removed. Imported leads were kept.');
    }

    private function validated(Request $request, ?LeadSource $source = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'driver' => ['required', 'in:hola,google_places'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:active,paused'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'bearer_token' => ['nullable', 'string', 'max:500'],
            'businesses_path' => ['nullable', 'string', 'max:255'],
            'query' => ['nullable', 'string', 'max:2000'],
            'region_code' => ['nullable', 'string', 'max:5'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:20'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
            'max_pages' => ['nullable', 'integer', 'min:1', 'max:20'],
            'default_project_type' => ['required', 'in:website,ecommerce,webapp,branding,seo,custom,other'],
            'auto_sync' => ['nullable', 'boolean'],
        ]);

        $credentials = array_filter([
            'api_key' => $request->input('api_key'),
            'bearer_token' => $request->input('bearer_token'),
        ], fn ($value) => filled($value));
        if ($source && !$credentials) $credentials = $source->credentials ?: [];

        $queries = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('query')))
            ->map(fn ($query) => trim($query))->filter()->values()->all();

        return $data + [
            'credentials' => $credentials,
            'settings' => [
                'businesses_path' => $request->input('businesses_path') ?: 'api/v1/businesses',
                'queries' => $queries,
                'region_code' => $request->input('region_code'),
                'page_size' => (int) ($request->input('page_size') ?: 20),
                'per_page' => (int) ($request->input('per_page') ?: 50),
                'max_pages' => (int) ($request->input('max_pages') ?: 5),
                'default_project_type' => $request->input('default_project_type'),
                'auto_sync' => $request->boolean('auto_sync'),
            ],
        ];
    }
}
