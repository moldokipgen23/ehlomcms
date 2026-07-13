<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('business_name', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create', [
            'client' => new Client,
            'products' => Product::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = $data['status'] ?? 'active';

        $client = Client::create($data);
        $client->products()->sync($this->productPivot($request));

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client created.')
            ->with('new_client_id', $client->id);
    }

    public function show(Client $client)
    {
        $client->load([
            'products', 'subscriptions.product', 'projects.products', 'projects.invoice',
            'invoices', 'domains', 'activities', 'agreements', 'tenant',
        ]);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $client->load('products');

        return view('clients.edit', [
            'client' => $client,
            'products' => Product::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $client->update($this->validated($request));
        $client->products()->sync($this->productPivot($request));

        return redirect()->route('clients.show', $client)->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,suspended',
            'products' => 'nullable|array',
            'products.*.product_id' => 'nullable|exists:products,id',
            'products.*.custom_price' => 'nullable|numeric|min:0',
        ]);
    }

    /**
     * Build the sync payload [product_id => [custom_price]] from the
     * repeatable assigned-products form rows.
     */
    private function productPivot(Request $request): array
    {
        $pivot = [];

        foreach ($request->input('products', []) as $row) {
            if (empty($row['product_id'])) {
                continue;
            }
            $price = $row['custom_price'] ?? '';
            $pivot[$row['product_id']] = [
                'custom_price' => $price === '' || $price === null ? null : (float) $price,
            ];
        }

        return $pivot;
    }
}
