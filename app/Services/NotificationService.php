<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Subscription;

class NotificationService
{
    /**
     * Memoized result for the current request.
     */
    private ?array $cache = null;

    /**
     * Collect grouped alerts from existing data.
     *
     * @return array{urgent: array, upcoming: array, drafts: array, count: int}
     */
    public function alerts(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $today = now()->startOfDay();
        $urgent = [];
        $upcoming = [];
        $drafts = [];

        // Subscriptions expiring within 30 days.
        foreach (
            Subscription::with('product')
                ->where('status', 'active')
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->orderBy('expiry_date')
                ->get() as $sub
        ) {
            $days = (int) $today->diffInDays($sub->expiry_date, false);
            $item = [
                'label' => ($sub->product->name ?? 'Subscription') . ' — ' . $this->daysLabel($days),
                'url' => route('subscriptions.index'),
                'icon' => 'ti-credit-card',
            ];

            if ($days <= 7) {
                $urgent[] = $item + ['level' => 'urgent'];
            } else {
                $upcoming[] = $item + ['level' => 'warning'];
            }
        }

        // Domains: expired, or expiring within 30 days.
        foreach (Domain::orderBy('expiry_date')->get() as $domain) {
            $days = $domain->days_until_expiry;

            if ($domain->is_expired && $domain->status === 'active') {
                $overdue = abs($days);
                $urgent[] = [
                    'label' => $domain->domain_name . ' — expired ' . $overdue . ' day' . ($overdue === 1 ? '' : 's') . ' ago',
                    'url' => route('infrastructure.index', ['tab' => 'registered']),
                    'icon' => 'ti-world',
                    'level' => 'critical',
                ];
            } elseif ($days >= 0 && $days <= 7) {
                $urgent[] = [
                    'label' => $domain->domain_name . ' — ' . $this->daysLabel($days),
                    'url' => route('infrastructure.index', ['tab' => 'registered']),
                    'icon' => 'ti-world',
                    'level' => 'urgent',
                ];
            } elseif ($days > 7 && $days <= 30) {
                $upcoming[] = [
                    'label' => $domain->domain_name . ' — ' . $this->daysLabel($days),
                    'url' => route('infrastructure.index', ['tab' => 'registered']),
                    'icon' => 'ti-world',
                    'level' => 'warning',
                ];
            }
        }

        // Unpaid invoices past their due date.
        foreach (
            Invoice::with('client')
                ->where('status', 'unpaid')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', $today)
                ->orderBy('due_date')
                ->get() as $invoice
        ) {
            $urgent[] = [
                'label' => $invoice->invoice_number . ' — ' . ($invoice->client->name ?? 'Client') . ' overdue',
                'url' => route('invoices.show', $invoice),
                'icon' => 'ti-file-invoice',
                'level' => 'urgent',
            ];
        }

        // Draft invoices waiting for review.
        foreach (
            Invoice::with('client')
                ->where('status', 'draft')
                ->latest()
                ->get() as $invoice
        ) {
            $drafts[] = [
                'label' => $invoice->invoice_number . ' — ' . ($invoice->client->name ?? 'Client'),
                'url' => route('invoices.show', $invoice),
                'icon' => 'ti-file-invoice',
                'level' => 'info',
            ];
        }

        // New leads that haven't been contacted yet.
        foreach (
            Lead::where('status', 'new')->latest()->get() as $lead
        ) {
            $urgent[] = [
                'label' => $lead->name . ' — new ' . ($lead->project_type ? \App\Models\Lead::PROJECT_TYPES[$lead->project_type] ?? $lead->project_type : 'lead') . ' inquiry',
                'url' => route('leads.show', $lead),
                'icon' => 'ti-user-star',
                'level' => 'urgent',
            ];
        }

        return $this->cache = [
            'urgent' => $urgent,
            'upcoming' => $upcoming,
            'drafts' => $drafts,
            'count' => count($urgent) + count($upcoming) + count($drafts),
        ];
    }

    /**
     * Human-friendly days-remaining label.
     */
    private function daysLabel(int $days): string
    {
        if ($days <= 0) {
            return 'expires today';
        }

        return $days . ' day' . ($days === 1 ? '' : 's') . ' left';
    }
}
