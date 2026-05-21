<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status;

        $agreements = Agreement::with('client')
            ->when(in_array($status, ['draft', 'sent', 'signed', 'cancelled']), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('agreements.index', compact('agreements', 'status'));
    }

    public function create(Request $request)
    {
        return view('agreements.create', [
            'agreement' => new Agreement([
                'client_id' => $request->client_id,
                'terms_and_conditions' => Agreement::defaultTerms(),
            ]),
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Agreement::create($this->validated($request));

        return redirect()->route('agreements.index')->with('success', 'Agreement created.');
    }

    public function show(Agreement $agreement)
    {
        $agreement->load('client');

        return view('agreements.show', compact('agreement'));
    }

    public function edit(Agreement $agreement)
    {
        return view('agreements.edit', [
            'agreement' => $agreement,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Agreement $agreement)
    {
        $agreement->update($this->validated($request));

        return redirect()->route('agreements.show', $agreement)->with('success', 'Agreement updated.');
    }

    public function destroy(Agreement $agreement)
    {
        $agreement->delete();

        return redirect()->route('agreements.index')->with('success', 'Agreement deleted.');
    }

    public function downloadPdf(Agreement $agreement)
    {
        $agreement->load('client');
        $pdf = Pdf::loadView('pdf.agreement', compact('agreement'));

        return $pdf->download('agreement-' . $agreement->id . '.pdf');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:website,hosting,maintenance,ecommerce,other',
            'scope' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'payment_terms' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'terms_and_conditions' => 'nullable|string',
            'status' => 'required|in:draft,sent,signed,cancelled',
            'notes' => 'nullable|string',
        ]);
    }
}
