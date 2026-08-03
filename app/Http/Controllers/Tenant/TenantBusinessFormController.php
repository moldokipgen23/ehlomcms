<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantBusinessEnquiry;
use App\Models\TenantBusinessItem;
use App\Models\TenantNewsletterSubscriber;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantBusinessFormController extends Controller
{
    public function enquiry(Request $request): RedirectResponse
    {
        $school = $this->isSchool();
        $tenant = $this->tenant($school ? 'enquiry_form' : 'enquiries');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => [$school ? 'nullable' : 'required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'], 'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);
        TenantBusinessEnquiry::create($data + ['tenant_id' => $tenant->id, 'type' => $school ? 'admission_enquiry' : 'enquiry', 'status' => 'new']);
        return back()->with('success', 'Thank you. Your enquiry has been sent.');
    }

    public function newsletter(Request $request): RedirectResponse
    {
        $tenant = $this->tenant('newsletter');
        $data = $request->validate(['name' => ['nullable', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255']]);
        TenantNewsletterSubscriber::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => $data['email']],
            ['name' => $data['name'] ?? null, 'status' => 'active']
        );
        return back()->with('success', 'You are subscribed.');
    }

    public function career(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->tenant('careers');
        $career = TenantBusinessItem::where('tenant_id', $tenant->id)->where('type', 'career')->where('is_active', true)->findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'], 'message' => ['required', 'string', 'max:5000'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);
        $meta = ['career_id' => $career->id, 'career_title' => $career->title];
        if ($request->hasFile('resume')) {
            $meta['resume'] = $request->file('resume')->store("tenants/{$tenant->id}/career-applications", 'public');
        }
        TenantBusinessEnquiry::create([
            'tenant_id' => $tenant->id, 'type' => 'career_application', 'name' => $data['name'],
            'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'subject' => 'Application: ' . $career->title,
            'message' => $data['message'], 'meta' => $meta, 'status' => 'new',
        ]);
        return back()->with('success', 'Your application has been submitted.');
    }

    private function tenant(string $module)
    {
        $tenant = app(TenantContext::class)->get();
        abort_if(!in_array($tenant->site_type, ['business', 'school'], true) || !$tenant->hasModule($module), 404);
        return $tenant;
    }

    private function isSchool(): bool
    {
        return app(TenantContext::class)->get()?->site_type === 'school';
    }
}
