<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantBlogPost;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantBlogController extends Controller
{
    private function requireModule(string $key): void
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!$tenant->hasModule($key), 404);
    }

    public function index(): View
    {
        $this->requireModule('blog');
        $tenant = app(TenantContext::class)->get();
        $posts = TenantBlogPost::where('tenant_id', $tenant->id)->orderByDesc('created_at')->get();

        return view('tenant.blog.index', compact('tenant', 'posts'));
    }

    public function create(): View
    {
        $this->requireModule('blog');
        $tenant = app(TenantContext::class)->get();

        return view('tenant.blog.form', compact('tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireModule('blog');
        $tenant = app(TenantContext::class)->get();

        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($tenant->id, $data['title']);
        $data['tenant_id'] = $tenant->id;

        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = $request->file('cover_photo')->store('tenants/' . $tenant->id . '/blog', 'public');
        }

        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        TenantBlogPost::create($data);

        return redirect()->route('tenant.blog')->with('success', 'Post saved.');
    }

    public function edit(string $subdomain, int $id): View
    {
        $this->requireModule('blog');
        $tenant = app(TenantContext::class)->get();
        $post = TenantBlogPost::where('tenant_id', $tenant->id)->findOrFail($id);

        return view('tenant.blog.form', compact('tenant', 'post'));
    }

    public function update(Request $request, string $subdomain, int $id): RedirectResponse
    {
        $this->requireModule('blog');
        $tenant = app(TenantContext::class)->get();
        $post = TenantBlogPost::where('tenant_id', $tenant->id)->findOrFail($id);

        $data = $this->validated($request);

        if ($request->hasFile('cover_photo')) {
            if ($post->cover_photo) {
                Storage::disk('public')->delete($post->cover_photo);
            }
            $data['cover_photo'] = $request->file('cover_photo')->store('tenants/' . $tenant->id . '/blog', 'public');
        }

        if ($data['status'] === 'published' && $post->status !== 'published') {
            $data['published_at'] = now();
        }

        $post->update($data);

        return redirect()->route('tenant.blog')->with('success', 'Post updated.');
    }

    public function destroy(string $subdomain, int $id): RedirectResponse
    {
        $this->requireModule('blog');
        $tenant = app(TenantContext::class)->get();
        $post = TenantBlogPost::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($post->cover_photo) {
            Storage::disk('public')->delete($post->cover_photo);
        }

        $post->delete();

        return redirect()->route('tenant.blog')->with('success', 'Post deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);
    }

    private function uniqueSlug(int $tenantId, string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (TenantBlogPost::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
