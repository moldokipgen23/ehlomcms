<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminTenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::with('client')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('tenants.form', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subdomain' => ['required', 'string', 'max:255', 'unique:tenants,subdomain', 'regex:/^[a-z0-9-]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'site_type' => ['required', Rule::in(['shopping', 'info'])],
            'template_id' => ['nullable', Rule::in(['shop', 'info'])],
            'plan' => ['nullable', 'string', 'max:255'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'action_type' => ['nullable', Rule::in(['whatsapp', 'razorpay'])],
        ]);

        $data['status'] = 'active';

        Tenant::create($data);

        return redirect()->route('tenants.index')->with('success', 'Tenant created.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'status' => $tenant->status === 'active' ? 'suspended' : 'active',
        ]);

        $label = $tenant->status === 'active' ? 'activated' : 'suspended';

        return redirect()->route('tenants.index')->with('success', "{$tenant->name} {$label}.");
    }
}
