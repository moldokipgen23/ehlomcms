<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantGalleryImage;
use App\Services\TenantContext;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();

        $galleryCount = TenantGalleryImage::where('tenant_id', $tenant->id)->count();

        return view('tenant.dashboard.index', compact('tenant', 'galleryCount'));
    }
}
