<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\View\View;

class TenantPolicyPageController extends Controller
{
    private array $pages = [
        'privacy-policy' => ['title' => 'Privacy Policy', 'field' => 'privacy_policy'],
        'terms-and-conditions' => ['title' => 'Terms & Conditions', 'field' => 'terms_conditions'],
        'refund-policy' => ['title' => 'Refund Policy', 'field' => 'refund_policy'],
        'shipping-policy' => ['title' => 'Shipping Policy', 'field' => 'shipping_policy'],
    ];

    public function show(string $slug): View
    {
        abort_unless(isset($this->pages[$slug]), 404);

        $tenant = app(TenantContext::class)->get();
        $page = $this->pages[$slug];
        $settings = $tenant->theme_settings ?? [];
        $content = $settings[$page['field']] ?? null;

        return view('tenant-templates.shop.policy', compact('tenant', 'page', 'content', 'slug'));
    }
}
