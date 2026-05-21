<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->where('category', 'custom')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(Request $request)
    {
        $category = array_key_exists($request->category, Product::CATEGORIES)
            ? $request->category
            : 'custom';

        return view('products.create', ['product' => new Product(['category' => $category])]);
    }

    public function store(Request $request)
    {
        $product = Product::create($this->validated($request));

        return $this->redirectFor($product)->with('success', $this->label($product) . ' created.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request));

        return $this->redirectFor($product)->with('success', $this->label($product) . ' updated.');
    }

    public function destroy(Product $product)
    {
        $redirect = $this->redirectFor($product);
        $product->delete();

        return $redirect->with('success', $this->label($product) . ' deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(Product::CATEGORIES)),
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,one_time',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
    }

    /**
     * Custom products live under Products & Services; hosting and domain
     * catalog items live under the Domains & Hosting hub.
     */
    private function redirectFor(Product $product)
    {
        return $product->category === 'custom'
            ? redirect()->route('products.index')
            : redirect()->route('infrastructure.index', ['tab' => $product->category]);
    }

    private function label(Product $product): string
    {
        return match ($product->category) {
            'hosting' => 'Hosting plan',
            'domain' => 'Domain price',
            default => 'Product',
        };
    }
}
