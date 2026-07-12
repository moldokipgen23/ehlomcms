<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\View\View;

class TenantHomeController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();

        $templateId = $tenant->template_id ?? 'info';

        if ($templateId === 'shop') {
            $products = $tenant->products()->orderBy('name')->get();
        } else {
            $products = collect();
            $templateId = 'info';
        }

        $tenant->load('galleryImages');

        return view("tenant-templates.{$templateId}.index", compact('tenant', 'products'));
    }
}
