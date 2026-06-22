<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['create', 'store', 'thankyou']);
    }

    public function index(Request $request)
    {
        $leads = Lead::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('business_name', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->project_type, fn ($q, $t) => $q->where('project_type', $t))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leads.index', compact('leads'));
    }

    public function create()
    {
        return view('leads.create', ['lead' => new Lead]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'business_name' => 'nullable|string|max:255',
            'project_type' => 'nullable|string|in:' . implode(',', array_keys(Lead::PROJECT_TYPES)),
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'timeline' => 'nullable|string|in:' . implode(',', array_keys(Lead::TIMELINES)),
            'source' => 'nullable|string|in:' . implode(',', array_keys(Lead::SOURCES)),
        ]);

        $lead = Lead::create($data);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Thank you! We will get back to you soon.']);
        }

        return redirect()->route('leads.thankyou');
    }

    public function show(Lead $lead)
    {
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'business_name']);

        return view('leads.edit', compact('lead', 'clients'));
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'business_name' => 'nullable|string|max:255',
            'project_type' => 'nullable|string|in:' . implode(',', array_keys(Lead::PROJECT_TYPES)),
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'timeline' => 'nullable|string|in:' . implode(',', array_keys(Lead::TIMELINES)),
            'source' => 'nullable|string|in:' . implode(',', array_keys(Lead::SOURCES)),
            'status' => 'required|string|in:' . implode(',', array_keys(Lead::STATUSES)),
            'notes' => 'nullable|string',
            'converted_client_id' => 'nullable|exists:clients,id',
        ]);

        $lead->update($data);

        return redirect()->route('leads.show', $lead)->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    public function thankyou()
    {
        return view('leads.thankyou');
    }

    public function convert(Lead $lead)
    {
        $client = Client::create([
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone ?: '0000000000',
            'business_name' => $lead->business_name,
            'notes' => 'Converted from lead.' . ($lead->description ? "\n\nRequirements:\n" . $lead->description : ''),
            'status' => 'active',
        ]);

        $lead->update([
            'status' => 'won',
            'converted_client_id' => $client->id,
        ]);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Lead converted to client successfully.');
    }
}
