<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceService
{
    /**
     * Generate the next invoice number in INV-YYYY-NNN format.
     */
    public function nextInvoiceNumber(): string
    {
        $year = date('Y');

        $last = Invoice::where('invoice_number', 'like', "INV-{$year}-%")
            ->orderByDesc('invoice_number')
            ->first();

        $next = $last ? ((int) substr($last->invoice_number, -3)) + 1 : 1;

        return 'INV-' . $year . '-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Build clean line items and totals from raw request input.
     *
     * @return array{items: array<int, array>, subtotal: float, tax_amount: float, total: float}
     */
    public function buildLineItems(array $rawItems, float $taxRate = 0): array
    {
        $items = [];
        $subtotal = 0;

        foreach ($rawItems as $row) {
            if (empty($row['description'])) {
                continue;
            }
            $qty = (float) ($row['quantity'] ?? 0);
            $unit = (float) ($row['unit_price'] ?? 0);
            $lineTotal = round($qty * $unit, 2);
            $subtotal += $lineTotal;

            $items[] = [
                'description' => $row['description'],
                'quantity' => $qty,
                'unit_price' => $unit,
                'total' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $taxAmount = round($subtotal * $taxRate / 100, 2);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => round($subtotal + $taxAmount, 2),
        ];
    }
}
