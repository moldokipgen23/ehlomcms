<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminExpenseController extends Controller
{
    private const CATEGORIES = ['hosting', 'software', 'salary', 'marketing', 'other'];

    public function index(): View
    {
        $expenses = Expense::orderByDesc('expense_date')->get();
        $total = $expenses->sum('amount');
        $byCategory = $expenses->groupBy('category')->map(fn($g) => $g->sum('amount'));
        $categories = self::CATEGORIES;
        return view('expenses.index', compact('expenses', 'total', 'byCategory', 'categories'));
    }

    public function create(): View
    {
        $categories = self::CATEGORIES;
        return view('expenses.form', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:' . implode(',', self::CATEGORIES),
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        Expense::create($validated);

        AuditLog::log('expense_created', "Expense of ₹{$validated['amount']} recorded in {$validated['category']}", 'expense');

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        AuditLog::log('expense_deleted', "Expense of ₹{$expense->amount} deleted", 'expense', $expense->id);

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}
