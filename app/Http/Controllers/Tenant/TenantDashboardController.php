<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantAddon;
use App\Models\TenantCustomPage;
use App\Models\TenantBlogPost;
use App\Models\TenantBusinessEnquiry;
use App\Models\TenantBusinessItem;
use App\Models\TenantNewsletterSubscriber;
use App\Models\TenantService;
use App\Models\TenantTestimonial;
use App\Models\TenantGalleryImage;
use App\Models\TenantOrder;
use App\Models\TenantOrderItem;
use App\Models\TenantProduct;
use App\Models\TenantProductVariant;
use App\Models\Theme;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function index(): View
    {
        $tenant = app(TenantContext::class)->get();

        if ($tenant->site_mode === 'static') {
            $tenant->loadMissing(['client', 'hostingPlan']);
            $theme = Theme::where('key', $tenant->template_id)->first();

            return view('tenant.dashboard.static', compact('tenant', 'theme'));
        }

        $galleryCount = TenantGalleryImage::where('tenant_id', $tenant->id)->count();
        $productCount = TenantProduct::where('tenant_id', $tenant->id)->count();
        $activeProductCount = TenantProduct::where('tenant_id', $tenant->id)->where('is_active', true)->count();

        $orders = TenantOrder::where('tenant_id', $tenant->id)->latest()->get();
        $orderCount = $orders->count();
        $revenueOrders = $orders
            ->reject(fn (TenantOrder $order) => in_array($order->status, ['cancelled', 'failed', 'refunded'], true))
            ->values();
        $revenueTotal = $revenueOrders->sum(fn (TenantOrder $order) => (float) ($order->total ?: $order->amount ?: 0));
        $todayRevenue = $revenueOrders
            ->filter(fn (TenantOrder $order) => $order->created_at?->isToday())
            ->sum(fn (TenantOrder $order) => (float) ($order->total ?: $order->amount ?: 0));
        $monthRevenue = $revenueOrders
            ->filter(fn (TenantOrder $order) => $order->created_at?->isCurrentMonth())
            ->sum(fn (TenantOrder $order) => (float) ($order->total ?: $order->amount ?: 0));
        $averageOrderValue = $revenueOrders->count() ? $revenueTotal / $revenueOrders->count() : 0;
        $lastSale = $revenueOrders->first();
        $pendingOrderCount = $orders->whereIn('status', ['pending', 'new', 'processing'])->count();
        $paidOrderCount = $orders->filter(fn (TenantOrder $order) => in_array($order->payment_status, ['paid', 'captured'], true) || in_array($order->status, ['paid', 'completed', 'delivered'], true))->count();

        $recentOrders = $orders->take(5);
        $topProducts = TenantOrderItem::query()
            ->join('tenant_orders', 'tenant_order_items.tenant_order_id', '=', 'tenant_orders.id')
            ->where('tenant_orders.tenant_id', $tenant->id)
            ->whereNotIn('tenant_orders.status', ['cancelled', 'failed', 'refunded'])
            ->select(
                DB::raw("COALESCE(tenant_order_items.product_name, 'Product') as product_name"),
                DB::raw('SUM(tenant_order_items.quantity) as units_sold'),
                DB::raw('SUM(COALESCE(tenant_order_items.total_price, tenant_order_items.unit_price * tenant_order_items.quantity, 0)) as sales_total')
            )
            ->groupBy(DB::raw("COALESCE(tenant_order_items.product_name, 'Product')"))
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();
        $activeAddonCount = TenantAddon::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        $activeModuleCount = count($tenant->modules ?? []);
        $customPageCount = TenantCustomPage::where('tenant_id', $tenant->id)->count();
        $publishedCustomPageCount = TenantCustomPage::where('tenant_id', $tenant->id)->where('is_published', true)->count();
        $policyFields = ['privacy_policy', 'terms_conditions', 'refund_policy', 'shipping_policy'];
        $policyReadyCount = collect($policyFields)
            ->filter(fn (string $field) => filled($tenant->theme_settings[$field] ?? null))
            ->count();
        $inventoryUnits = TenantProduct::where('tenant_id', $tenant->id)->sum('stock');
        $lowStockCount = TenantProduct::where('tenant_id', $tenant->id)
            ->whereNotNull('stock')
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->count();

        $businessStats = [];
        $recentEnquiries = collect();
        $schoolStats = [];
        $recentSchoolItems = collect();
        if ($tenant->site_type === 'business') {
            $businessStats = [
                'services' => TenantService::where('tenant_id', $tenant->id)->count(),
                'case_studies' => TenantBusinessItem::where('tenant_id', $tenant->id)->where('type', 'case_study')->count(),
                'team' => TenantBusinessItem::where('tenant_id', $tenant->id)->where('type', 'team_member')->count(),
                'careers' => TenantBusinessItem::where('tenant_id', $tenant->id)->where('type', 'career')->where('is_active', true)->count(),
                'testimonials' => TenantTestimonial::where('tenant_id', $tenant->id)->count(),
                'posts' => TenantBlogPost::where('tenant_id', $tenant->id)->where('status', 'published')->count(),
                'enquiries' => TenantBusinessEnquiry::where('tenant_id', $tenant->id)->count(),
                'new_enquiries' => TenantBusinessEnquiry::where('tenant_id', $tenant->id)->where('status', 'new')->count(),
                'subscribers' => TenantNewsletterSubscriber::where('tenant_id', $tenant->id)->where('status', 'active')->count(),
            ];
            $recentEnquiries = TenantBusinessEnquiry::where('tenant_id', $tenant->id)->latest()->limit(5)->get();
        }

        if ($tenant->site_type === 'school') {
            $schoolStats = [
                'published_pages' => $publishedCustomPageCount,
                'gallery_images' => $galleryCount,
                'academic_programs' => TenantBusinessItem::where('tenant_id', $tenant->id)->where('type', 'academic_program')->where('is_active', true)->count(),
                'faculty' => TenantBusinessItem::where('tenant_id', $tenant->id)->where('type', 'faculty_member')->where('is_active', true)->count(),
                'facilities' => TenantBusinessItem::where('tenant_id', $tenant->id)->where('type', 'facility')->where('is_active', true)->count(),
                'notices' => TenantBusinessItem::where('tenant_id', $tenant->id)->where('type', 'school_notice')->where('is_active', true)->count(),
                'enquiries' => TenantBusinessEnquiry::where('tenant_id', $tenant->id)->where('type', 'admission_enquiry')->count(),
                'new_enquiries' => TenantBusinessEnquiry::where('tenant_id', $tenant->id)->where('type', 'admission_enquiry')->where('status', 'new')->count(),
            ];
            $recentEnquiries = TenantBusinessEnquiry::where('tenant_id', $tenant->id)
                ->where('type', 'admission_enquiry')
                ->latest()->limit(5)->get();
            $recentSchoolItems = TenantBusinessItem::where('tenant_id', $tenant->id)
                ->whereIn('type', ['academic_program', 'faculty_member', 'facility', 'school_notice', 'achievement'])
                ->latest()->limit(6)->get();
        }

        if ($tenant->hasModule('variants')) {
            $variantQuery = TenantProductVariant::whereHas('product', fn ($query) => $query->where('tenant_id', $tenant->id));
            $inventoryUnits += (clone $variantQuery)->sum('stock');
            $lowStockCount += (clone $variantQuery)
                ->whereNotNull('stock')
                ->where('stock', '>', 0)
                ->where('stock', '<=', 5)
                ->count();
        }

        return view('tenant.dashboard.index', compact(
            'tenant',
            'galleryCount',
            'productCount',
            'activeProductCount',
            'orderCount',
            'revenueTotal',
            'todayRevenue',
            'monthRevenue',
            'averageOrderValue',
            'lastSale',
            'pendingOrderCount',
            'paidOrderCount',
            'topProducts',
            'recentOrders',
            'activeAddonCount',
            'activeModuleCount',
            'customPageCount',
            'publishedCustomPageCount',
            'policyReadyCount',
            'inventoryUnits',
            'lowStockCount',
            'businessStats',
            'recentEnquiries',
            'schoolStats',
            'recentSchoolItems',
        ));
    }
}
