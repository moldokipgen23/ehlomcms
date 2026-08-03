<?php

namespace App\Http\Controllers;

use App\Models\AiPrototypeCatalog;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminPrototypeCatalogController extends Controller
{
    public function index(): View
    {
        return view('prototype-catalog.index', [
            'prototypes' => AiPrototypeCatalog::orderBy('business_type')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('prototype-catalog.form', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        AiPrototypeCatalog::create($this->validated($request));

        return redirect()->route('prototype-catalog.index')->with('success', 'Prototype added to the matching catalog.');
    }

    public function edit(AiPrototypeCatalog $prototype): View
    {
        return view('prototype-catalog.form', $this->formData($prototype));
    }

    public function update(Request $request, AiPrototypeCatalog $prototype): RedirectResponse
    {
        $prototype->update($this->validated($request, $prototype));

        return redirect()->route('prototype-catalog.index')->with('success', 'Prototype matching updated.');
    }

    public function toggle(AiPrototypeCatalog $prototype): RedirectResponse
    {
        $prototype->update(['status' => $prototype->status === 'active' ? 'paused' : 'active']);

        return back()->with('success', $prototype->name . ' is now ' . $prototype->status . '.');
    }

    private function formData(?AiPrototypeCatalog $prototype = null): array
    {
        return [
            'prototype' => $prototype,
            'businessTypes' => config('business_types', []),
            'themes' => Theme::orderBy('name')->get(['key', 'name', 'industries']),
        ];
    }

    private function validated(Request $request, ?AiPrototypeCatalog $prototype = null): array
    {
        $keyRule = 'required|string|max:100|alpha_dash';
        if ($prototype) {
            $keyRule .= '|unique:ai_prototype_catalog,key,' . $prototype->id;
        } else {
            $keyRule .= '|unique:ai_prototype_catalog,key';
        }

        $data = $request->validate([
            'key' => $keyRule,
            'name' => 'required|string|max:255',
            'business_type' => 'required|string|in:' . implode(',', array_keys(config('business_types', []))),
            'theme_key' => 'nullable|string|exists:themes,key',
            'preview_url' => 'nullable|url|max:500',
            'recommended_offer' => 'nullable|string|max:255',
            'keywords' => 'nullable|string|max:2000',
            'status' => 'required|in:active,paused',
        ]);

        $data['keywords'] = collect(preg_split('/[\r\n,]+/', $data['keywords'] ?? ''))
            ->map(fn ($keyword) => Str::lower(trim($keyword)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $data;
    }
}
