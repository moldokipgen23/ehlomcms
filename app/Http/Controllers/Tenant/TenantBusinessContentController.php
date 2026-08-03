<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantBusinessItem;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantBusinessContentController extends Controller
{
    private const TYPES = [
        'case-studies' => ['module' => 'case_studies', 'type' => 'case_study', 'singular' => 'Case Study', 'plural' => 'Case Studies', 'subtitle' => 'Client / Result'],
        'team' => ['module' => 'team', 'type' => 'team_member', 'singular' => 'Team Member', 'plural' => 'Team', 'subtitle' => 'Role / Title'],
        'careers' => ['module' => 'careers', 'type' => 'career', 'singular' => 'Job Opening', 'plural' => 'Careers', 'subtitle' => 'Location / Work Type'],
        'academics' => ['module' => 'academics', 'type' => 'academic_program', 'singular' => 'Academic Program', 'plural' => 'Academic Programs', 'subtitle' => 'Age Group / Class Range'],
        'faculty' => ['module' => 'faculty', 'type' => 'faculty_member', 'singular' => 'Faculty Member', 'plural' => 'Faculty & Staff', 'subtitle' => 'Role / Qualification'],
        'facilities' => ['module' => 'facilities', 'type' => 'facility', 'singular' => 'Facility', 'plural' => 'Facilities', 'subtitle' => 'Short Highlight'],
        'activities' => ['module' => 'student_life', 'type' => 'student_activity', 'singular' => 'Student Life Item', 'plural' => 'Student Life', 'subtitle' => 'Activity Type'],
        'achievements' => ['module' => 'achievements', 'type' => 'achievement', 'singular' => 'Achievement', 'plural' => 'Achievements', 'subtitle' => 'Year / Category'],
        'notices' => ['module' => 'news', 'type' => 'school_notice', 'singular' => 'News or Notice', 'plural' => 'News & Notices', 'subtitle' => 'Date / Notice Type'],
    ];

    public function index(string $type): View
    {
        [$tenant, $definition] = $this->context($type);
        $items = TenantBusinessItem::where('tenant_id', $tenant->id)
            ->where('type', $definition['type'])
            ->orderBy('sort_order')->orderBy('title')->get();

        return view('tenant.business-content.index', compact('tenant', 'items', 'type', 'definition'));
    }

    public function create(string $type): View
    {
        [$tenant, $definition] = $this->context($type);
        return view('tenant.business-content.form', compact('tenant', 'type', 'definition'));
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        [$tenant, $definition] = $this->context($type);
        $data = $this->validated($request, $tenant->id, $definition['type']);
        $data['tenant_id'] = $tenant->id;
        $data['type'] = $definition['type'];
        $data['image'] = $this->storeImage($request, $tenant->id, $definition['type']);
        TenantBusinessItem::create($data);

        return redirect()->route('tenant.business-content.index', ['type' => $type])->with('success', $definition['singular'] . ' added.');
    }

    public function edit(string $type, int $id): View
    {
        [$tenant, $definition] = $this->context($type);
        $item = $this->item($tenant->id, $definition['type'], $id);
        return view('tenant.business-content.form', compact('tenant', 'item', 'type', 'definition'));
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        [$tenant, $definition] = $this->context($type);
        $item = $this->item($tenant->id, $definition['type'], $id);
        $data = $this->validated($request, $tenant->id, $definition['type'], $item->id);
        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($item->image);
            $data['image'] = $this->storeImage($request, $tenant->id, $definition['type']);
        }
        $item->update($data);

        return redirect()->route('tenant.business-content.index', ['type' => $type])->with('success', $definition['singular'] . ' updated.');
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        [$tenant, $definition] = $this->context($type);
        $item = $this->item($tenant->id, $definition['type'], $id);
        Storage::disk('public')->delete($item->image);
        $item->delete();

        return redirect()->route('tenant.business-content.index', ['type' => $type])->with('success', $definition['singular'] . ' deleted.');
    }

    private function context(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $tenant = app(TenantContext::class)->get();
        $definition = self::TYPES[$type];
        abort_if(!in_array($tenant->site_type, ['business', 'school'], true) || !$tenant->hasModule($definition['module']), 404);
        return [$tenant, $definition];
    }

    private function validated(Request $request, int $tenantId, string $itemType, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'result' => ['nullable', 'string', 'max:255'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'deadline' => ['nullable', 'date'],
        ]);
        $baseSlug = Str::slug($data['slug'] ?: $data['title']);
        $slug = $baseSlug ?: Str::random(8);
        $suffix = 2;
        while (TenantBusinessItem::where('tenant_id', $tenantId)->where('type', $itemType)->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }
        return [
            'title' => $data['title'],
            'slug' => $slug,
            'subtitle' => $data['subtitle'] ?? null,
            'body' => $data['body'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'meta' => array_filter([
                'result' => $data['result'] ?? null,
                'external_url' => $data['external_url'] ?? null,
                'deadline' => $data['deadline'] ?? null,
            ]),
        ];
    }

    private function storeImage(Request $request, int $tenantId, string $type): ?string
    {
        return $request->hasFile('image')
            ? $request->file('image')->store("tenants/{$tenantId}/business/{$type}", 'public')
            : null;
    }

    private function item(int $tenantId, string $type, int $id): TenantBusinessItem
    {
        return TenantBusinessItem::where('tenant_id', $tenantId)->where('type', $type)->findOrFail($id);
    }
}
