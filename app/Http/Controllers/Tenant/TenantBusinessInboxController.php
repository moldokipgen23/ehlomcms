<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantBusinessEnquiry;
use App\Models\TenantNewsletterSubscriber;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantBusinessInboxController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->tenant();
        $tab = $request->query('tab') === 'subscribers' ? 'subscribers' : 'enquiries';
        $enquiries = TenantBusinessEnquiry::where('tenant_id', $tenant->id)->latest()->get();
        $subscribers = TenantNewsletterSubscriber::where('tenant_id', $tenant->id)->latest()->get();
        return view('tenant.business-inbox.index', compact('tenant', 'tab', 'enquiries', 'subscribers'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        $data = $request->validate(['status' => ['required', 'in:new,contacted,closed']]);
        TenantBusinessEnquiry::where('tenant_id', $tenant->id)->findOrFail($id)->update($data);
        return back()->with('success', 'Enquiry status updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $tenant = $this->tenant();
        TenantBusinessEnquiry::where('tenant_id', $tenant->id)->findOrFail($id)->delete();
        return back()->with('success', 'Enquiry deleted.');
    }

    private function tenant()
    {
        $tenant = app(TenantContext::class)->get();
        $allowed = $tenant->site_type === 'business'
            ? $tenant->hasModule('enquiries')
            : ($tenant->site_type === 'school' && $tenant->hasModule('enquiry_form'));
        abort_if(!$allowed, 404);
        return $tenant;
    }
}
