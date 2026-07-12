<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $ownerName = $data['owner_name'];
        $ownerEmail = $data['owner_email'];
        unset($data['owner_name'], $data['owner_email']);

        $data['status'] = 'active';

        $tenant = Tenant::create($data);

        // The tenant's owner account is created here, by the agency, rather
        // than through public self-registration - see routes/tenant.php for
        // why open registration was removed. The generated password is only
        // ever available in this one post-redirect flash message; it isn't
        // stored anywhere in plaintext or logged.
        $generatedPassword = Str::password(14);

        User::create([
            'name' => $ownerName,
            'email' => $ownerEmail,
            'password' => Hash::make($generatedPassword),
            'tenant_id' => $tenant->id,
        ]);

        return redirect()->route('tenants.index')->with('success', "Tenant created.")
            ->with('generated_login', [
                'subdomain' => $tenant->subdomain,
                'email' => $ownerEmail,
                'password' => $generatedPassword,
            ]);
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
