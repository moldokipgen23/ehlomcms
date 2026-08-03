<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\EhlomBillingFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::with('invoice.client')->orderByDesc('payment_date')->get();
        $total = $payments->where('status', 'paid')->sum('amount');
        return view('payments.index', compact('payments', 'total'));
    }

    public function create(): View
    {
        $invoices = Invoice::orderByDesc('created_at')->get(['id', 'invoice_number', 'client_id', 'total']);
        return view('payments.form', compact('invoices'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method' => 'required|in:bank_transfer,cash,cheque,online,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment = Payment::create($validated);

        if ($payment->invoice_id) {
            $invoice = $payment->invoice;
            $paidTotal = Payment::where('invoice_id', $invoice->id)->where('status', 'paid')->sum('amount');
            if ($paidTotal >= $invoice->total) {
                $invoice->update(['status' => 'paid']);
            } elseif ($paidTotal > 0) {
                $invoice->update(['status' => 'partial']);
            }

            app(EhlomBillingFulfillmentService::class)->fulfillInvoice($invoice->fresh());
        }

        AuditLog::log('payment_created', "Payment of ₹{$validated['amount']} recorded", 'payment', $payment->id, $validated);

        return redirect()->route('payments.index')->with('success', 'Payment recorded.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        AuditLog::log('payment_deleted', "Payment of ₹{$payment->amount} deleted", 'payment', $payment->id);

        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Payment deleted.');
    }
}
