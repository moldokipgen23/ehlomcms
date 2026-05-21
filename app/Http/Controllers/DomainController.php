<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Domain;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function create(Request $request)
    {
        return view('domains.create', [
            'domain' => new Domain(['client_id' => $request->client_id]),
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Domain::create($this->validated($request));

        return redirect()->route('infrastructure.index', ['tab' => 'registered'])->with('success', 'Domain added.');
    }

    public function edit(Domain $domain)
    {
        return view('domains.edit', [
            'domain' => $domain,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Domain $domain)
    {
        $domain->update($this->validated($request));

        return redirect()->route('infrastructure.index', ['tab' => 'registered'])->with('success', 'Domain updated.');
    }

    public function destroy(Domain $domain)
    {
        $domain->delete();

        return redirect()->back()->with('success', 'Domain deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'domain_name' => 'required|string|max:255',
            'registrar' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'expiry_date' => 'required|date',
            'renewal_cost' => 'nullable|numeric|min:0',
            'hosting_server' => 'nullable|string|max:255',
            'hosting_plan' => 'nullable|string|max:255',
            'nameserver' => 'nullable|string|max:255',
            'cloudpanel_notes' => 'nullable|string',
            'status' => 'required|in:active,expired,transferred',
            'notes' => 'nullable|string',
        ]);
    }
}
