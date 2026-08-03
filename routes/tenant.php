<?php

// Tenant-scoped routes (subdomain-based).
// Routes here are only reachable when a tenant has been resolved by
// ResolveTenant middleware (see app/Http/Middleware/ResolveTenant.php).
// These routes run INSIDE a tenant context — TenantContext::get() is
// guaranteed to return a valid, active tenant.
//
// Local testing:
//   Add entries to /etc/hosts:
//     127.0.0.1 testshop1.ehlom-os.test
//     127.0.0.1 testinfo1.ehlom-os.test
//   Then set APP_TENANT_DOMAIN=ehlom-os.test in .env
//   Visit http://testshop1.ehlom-os.test in your browser.
//
//   Alternatively, use Herd/Valet with `herd link ehlom-os --secure`
//   and access tenant subdomains directly.

use App\Http\Controllers\Tenant\Auth\TenantLoginController;
use App\Http\Controllers\Tenant\Auth\TenantForgotPasswordController;
use App\Http\Controllers\Tenant\Auth\TenantNewPasswordController;
use App\Http\Controllers\Tenant\TenantAddonController;
use App\Http\Controllers\Tenant\TenantCartController;
use App\Http\Controllers\Tenant\TenantCommerceFeatureController;
use App\Http\Controllers\Tenant\TenantCatalogController;
use App\Http\Controllers\Tenant\TenantCollectionController;
use App\Http\Controllers\Tenant\TenantContentController;
use App\Http\Controllers\Tenant\TenantCouponController;
use App\Http\Controllers\Tenant\TenantCustomPageController;
use App\Http\Controllers\Tenant\TenantCustomerAccountController;
use App\Http\Controllers\Tenant\TenantCustomerController;
use App\Http\Controllers\Tenant\TenantDashboardController;
use App\Http\Controllers\Tenant\TenantHomeController;
use App\Http\Controllers\Tenant\TenantInstagramPostController;
use App\Http\Controllers\Tenant\TenantInventoryController;
use App\Http\Controllers\Tenant\TenantMarketingSectionController;
use App\Http\Controllers\Tenant\TenantOrderController;
use App\Http\Controllers\Tenant\TenantAttributeController;
use App\Http\Controllers\Tenant\TenantPaymentSettingsController;
use App\Http\Controllers\Tenant\TenantPolicyPageController;
use App\Http\Controllers\Tenant\TenantReservationController;
use App\Http\Controllers\Tenant\TenantServiceController;
use App\Http\Controllers\Tenant\TenantSeoController;
use App\Http\Controllers\Tenant\TenantReviewController;
use App\Http\Controllers\Tenant\TenantShippingRuleController;
use App\Http\Controllers\Tenant\TenantStoreReviewController;
use App\Http\Controllers\Tenant\TenantSettingsController;
use App\Http\Controllers\Tenant\TenantTestimonialController;
use App\Http\Controllers\Tenant\TenantBlogController;
use App\Http\Controllers\Tenant\TenantBusinessContentController;
use App\Http\Controllers\Tenant\TenantBusinessFormController;
use App\Http\Controllers\Tenant\TenantBusinessInboxController;
use App\Http\Controllers\Tenant\TenantAddonCheckoutController;
use App\Http\Controllers\Tenant\TenantInfrastructureCheckoutController;
use App\Http\Controllers\Tenant\TenantAiAssistantController;
use App\Http\Controllers\Tenant\TenantThemeController;
use App\Http\Controllers\Tenant\TenantTicketController;
use App\Http\Controllers\Tenant\TenantTrackController;
use App\Http\Controllers\Tenant\TenantImpersonateController;
use App\Http\Controllers\Tenant\TenantWishlistController;
use Illuminate\Support\Facades\Route;

// Tenant routes are resolved by ResolveTenant middleware which handles both
// subdomain (*.ehlom.com) and custom domain resolution. The middleware sets
// URL::defaults(['subdomain' => ...]) so the {subdomain} parameter is always
// available regardless of which domain the request arrives on.
Route::group([], function () {

Route::middleware('tenant')->group(function () {
    Route::get('/', [TenantHomeController::class, 'index'])->name('tenant.home');
    Route::get('products/{slug}', [TenantHomeController::class, 'showProduct'])->name('tenant.product.show');

    // Cart + Checkout (public storefront)
    // Deliberately plain {id} parameters, not Eloquent route-model binding -
    // see TenantCartController for why (domain-scoped routes break implicit
    // binding, and binding without a manual tenant_id check would resolve
    // ANY tenant's product/order by ID regardless of who owns it).
    Route::get('cart', [TenantCartController::class, 'index'])->name('tenant.cart');
    Route::post('cart/add/{id}', [TenantCartController::class, 'add'])->name('tenant.cart.add');
    Route::post('cart/update/{id}', [TenantCartController::class, 'update'])->name('tenant.cart.update');
    Route::post('cart/remove/{id}', [TenantCartController::class, 'remove'])->name('tenant.cart.remove');
    Route::get('checkout', [TenantCartController::class, 'checkout'])->name('tenant.checkout');
    Route::post('checkout', [TenantCartController::class, 'placeOrder'])->name('tenant.checkout.place');
    Route::get('checkout/pay/{id}', [TenantCartController::class, 'pay'])->name('tenant.checkout.pay');
    Route::post('checkout/pay/{id}/verify', [TenantCartController::class, 'verifyRazorpayPayment'])->name('tenant.checkout.pay.verify');
    Route::get('checkout/confirm/{id}', [TenantCartController::class, 'confirm'])->name('tenant.checkout.confirm');

    // Order tracking (public)
    Route::get('track', [TenantTrackController::class, 'show'])->name('tenant.track');
    Route::get('track/lookup', [TenantTrackController::class, 'lookup'])->name('tenant.track.lookup');
    Route::get('account', [TenantCustomerAccountController::class, 'showAuth'])->name('tenant.customer.auth');
    Route::post('account/register', [TenantCustomerAccountController::class, 'register'])->name('tenant.customer.register');
    Route::post('account/login', [TenantCustomerAccountController::class, 'login'])->name('tenant.customer.login');
    Route::get('account/profile', [TenantCustomerAccountController::class, 'account'])->name('tenant.customer.account');
    Route::post('account/logout', [TenantCustomerAccountController::class, 'logout'])->name('tenant.customer.logout');
    Route::get('wishlist', [TenantWishlistController::class, 'index'])->name('tenant.wishlist');
    Route::post('wishlist/{productId}', [TenantWishlistController::class, 'toggle'])->name('tenant.wishlist.toggle');
    Route::post('products/{productId}/reviews', [TenantStoreReviewController::class, 'store'])->name('tenant.reviews.storefront');
    Route::get('policies/{slug}', [TenantPolicyPageController::class, 'show'])->name('tenant.policy');
    Route::get('pages/{slug}', [TenantCustomPageController::class, 'show'])->name('tenant.custom-page.show');
    Route::post('business/enquiry', [TenantBusinessFormController::class, 'enquiry'])->name('tenant.business.enquiry');
    Route::post('business/newsletter', [TenantBusinessFormController::class, 'newsletter'])->name('tenant.business.newsletter');
    Route::post('business/careers/{id}/apply', [TenantBusinessFormController::class, 'career'])->name('tenant.business.career.apply');

    // Reservation request (public storefront, restaurant tenants) - creates a
    // 'pending' reservation, no login required, same guest pattern as checkout.
    Route::post('reserve', [TenantReservationController::class, 'store'])->name('tenant.reserve');

    // AI Assistant chat (public storefront)
    Route::post('ai-assistant/chat', [TenantAiAssistantController::class, 'chat'])->name('tenant.ai-assistant.chat');

// Add-on marketplace checkout (public - requires Razorpay)
    Route::get('addons/{addon}/checkout', [TenantAddonCheckoutController::class, 'create'])->middleware('tenant.auth')->name('tenant.addons.checkout');
    Route::post('addons/{addon}/checkout', [TenantAddonCheckoutController::class, 'checkout'])->middleware('tenant.auth')->name('tenant.addons.pay');
    Route::get('addons/success', [TenantAddonCheckoutController::class, 'success'])->middleware('tenant.auth')->name('tenant.addons.success');

    // Domain/Hosting marketplace
    Route::get('infrastructure', [TenantInfrastructureCheckoutController::class, 'index'])->middleware('tenant.auth')->name('tenant.infrastructure');
    Route::post('infrastructure/custom-domain', [TenantInfrastructureCheckoutController::class, 'requestCustomDomain'])->middleware('tenant.auth')->name('tenant.infrastructure.custom-domain');
    Route::get('infrastructure/{product}/checkout', [TenantInfrastructureCheckoutController::class, 'create'])->middleware('tenant.auth')->name('tenant.infrastructure.checkout');
    Route::post('infrastructure/{product}/checkout', [TenantInfrastructureCheckoutController::class, 'checkout'])->middleware('tenant.auth')->name('tenant.infrastructure.pay');
    Route::get('infrastructure/success', [TenantInfrastructureCheckoutController::class, 'success'])->middleware('tenant.auth')->name('tenant.infrastructure.success');
});

Route::middleware('tenant')->prefix('dashboard')->group(function () {

    // Guest routes. No public registration - a tenant's owner account is
    // created by the agency (AdminTenantController::store) at the same time
    // the tenant itself is created, not via self-signup. Leaving a public
    // register route on every subdomain would let any visitor create a
    // dashboard login for someone else's shop.
    Route::middleware('guest')->group(function () {
        Route::get('login', [TenantLoginController::class, 'create'])->name('tenant.login');
        Route::post('login', [TenantLoginController::class, 'store']);

        Route::get('forgot-password', [TenantForgotPasswordController::class, 'create'])->name('tenant.password.request');
        Route::post('forgot-password', [TenantForgotPasswordController::class, 'store'])->name('tenant.password.email');
        Route::get('reset-password/{token}', [TenantNewPasswordController::class, 'create'])->name('tenant.password.reset');
        Route::post('reset-password', [TenantNewPasswordController::class, 'store'])->name('tenant.password.update');

    });

    // Cross-domain impersonation handoff (see AdminImpersonateController
    // ::loginAsTenant). NOT inside the 'guest' group — if the admin already
    // has a session on this subdomain from a prior impersonation, the guest
    // middleware's RedirectIfAuthenticated would try route('dashboard') which
    // needs portalHost and crashes. The 'signed' middleware alone is
    // sufficient — it rejects invalid/expired URLs outright.
    Route::get('impersonate/consume', [TenantImpersonateController::class, 'consume'])
        ->middleware('signed')
        ->name('tenant.impersonate.consume');

    // Authenticated tenant routes
    Route::middleware(['tenant.auth', 'tenant.delivery'])->group(function () {
        Route::get('/', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');

        Route::get('settings', [TenantSettingsController::class, 'edit'])->name('tenant.settings');
        Route::post('settings', [TenantSettingsController::class, 'update']);
        Route::post('settings/logo', [TenantSettingsController::class, 'uploadLogo'])->name('tenant.settings.logo');
        Route::post('settings/banner', [TenantSettingsController::class, 'uploadBanner'])->name('tenant.settings.banner');
        Route::post('settings/favicon', [TenantSettingsController::class, 'uploadFavicon'])->name('tenant.settings.favicon');

        Route::get('content', [TenantContentController::class, 'index'])->name('tenant.content');
        Route::post('content/about', [TenantContentController::class, 'updateAbout'])->name('tenant.content.about');
        Route::post('content/contact', [TenantContentController::class, 'updateContact'])->name('tenant.content.contact');
        Route::post('content/gallery', [TenantContentController::class, 'storeGalleryImage'])->name('tenant.gallery.store');
        Route::delete('content/gallery/{id}', [TenantContentController::class, 'destroyGalleryImage'])->name('tenant.gallery.destroy');

        Route::get('pages', [TenantCustomPageController::class, 'index'])->name('tenant.custom-pages');
        Route::get('pages/create', [TenantCustomPageController::class, 'create'])->name('tenant.custom-pages.create');
        Route::post('pages', [TenantCustomPageController::class, 'store'])->name('tenant.custom-pages.store');
        Route::get('pages/{id}/edit', [TenantCustomPageController::class, 'edit'])->name('tenant.custom-pages.edit');
        Route::put('pages/{id}', [TenantCustomPageController::class, 'update'])->name('tenant.custom-pages.update');
        Route::delete('pages/{id}', [TenantCustomPageController::class, 'destroy'])->name('tenant.custom-pages.destroy');

        // Catalog (Products CRUD)
        Route::get('catalog', [TenantCatalogController::class, 'index'])->name('tenant.catalog');
        Route::get('catalog/create', [TenantCatalogController::class, 'create'])->name('tenant.catalog.create');
        Route::post('catalog', [TenantCatalogController::class, 'store'])->name('tenant.catalog.store');
        Route::get('catalog/{id}/edit', [TenantCatalogController::class, 'edit'])->name('tenant.catalog.edit');
        Route::put('catalog/{id}', [TenantCatalogController::class, 'update'])->name('tenant.catalog.update');
        Route::delete('catalog/{id}', [TenantCatalogController::class, 'destroy'])->name('tenant.catalog.destroy');

        Route::get('collections', [TenantCollectionController::class, 'index'])->name('tenant.collections');
        Route::get('collections/create', [TenantCollectionController::class, 'create'])->name('tenant.collections.create');
        Route::post('collections', [TenantCollectionController::class, 'store'])->name('tenant.collections.store');
        Route::get('collections/{id}/edit', [TenantCollectionController::class, 'edit'])->name('tenant.collections.edit');
        Route::put('collections/{id}', [TenantCollectionController::class, 'update'])->name('tenant.collections.update');
        Route::delete('collections/{id}', [TenantCollectionController::class, 'destroy'])->name('tenant.collections.destroy');

        Route::get('inventory', [TenantInventoryController::class, 'index'])->name('tenant.inventory');
        Route::post('inventory', [TenantInventoryController::class, 'update'])->name('tenant.inventory.update');

        Route::get('attributes', [TenantAttributeController::class, 'index'])->name('tenant.attributes');
        Route::post('attributes', [TenantAttributeController::class, 'store'])->name('tenant.attributes.store');
        Route::post('attributes/{id}/values', [TenantAttributeController::class, 'storeValue'])->name('tenant.attributes.values.store');
        Route::delete('attributes/{id}', [TenantAttributeController::class, 'destroy'])->name('tenant.attributes.destroy');

        Route::get('marketing-sections', [TenantMarketingSectionController::class, 'index'])->name('tenant.marketing-sections');
        Route::get('marketing-sections/create', [TenantMarketingSectionController::class, 'create'])->name('tenant.marketing-sections.create');
        Route::post('marketing-sections', [TenantMarketingSectionController::class, 'store'])->name('tenant.marketing-sections.store');
        Route::get('marketing-sections/{id}/edit', [TenantMarketingSectionController::class, 'edit'])->name('tenant.marketing-sections.edit');
        Route::put('marketing-sections/{id}', [TenantMarketingSectionController::class, 'update'])->name('tenant.marketing-sections.update');
        Route::delete('marketing-sections/{id}', [TenantMarketingSectionController::class, 'destroy'])->name('tenant.marketing-sections.destroy');

        Route::get('instagram', [TenantInstagramPostController::class, 'index'])->name('tenant.instagram');
        Route::get('instagram/create', [TenantInstagramPostController::class, 'create'])->name('tenant.instagram.create');
        Route::post('instagram', [TenantInstagramPostController::class, 'store'])->name('tenant.instagram.store');
        Route::get('instagram/{id}/edit', [TenantInstagramPostController::class, 'edit'])->name('tenant.instagram.edit');
        Route::put('instagram/{id}', [TenantInstagramPostController::class, 'update'])->name('tenant.instagram.update');
        Route::delete('instagram/{id}', [TenantInstagramPostController::class, 'destroy'])->name('tenant.instagram.destroy');

        // Payment Settings (only accessible when action_type=razorpay)
        Route::get('payments', [TenantPaymentSettingsController::class, 'edit'])->name('tenant.payments');
        Route::post('payments', [TenantPaymentSettingsController::class, 'update']);

        Route::get('seo', [TenantSeoController::class, 'edit'])->name('tenant.seo');
        Route::post('seo', [TenantSeoController::class, 'update'])->name('tenant.seo.update');
        Route::get('coupons', [TenantCouponController::class, 'index'])->name('tenant.coupons');
        Route::post('coupons', [TenantCouponController::class, 'store'])->name('tenant.coupons.store');
        Route::get('reviews', [TenantReviewController::class, 'index'])->name('tenant.reviews');
        Route::post('reviews/{id}', [TenantReviewController::class, 'update'])->name('tenant.reviews.update');
        Route::get('customers', [TenantCustomerController::class, 'index'])->name('tenant.customers');
        Route::get('shipping-rules', [TenantShippingRuleController::class, 'index'])->name('tenant.shipping-rules');
        Route::post('shipping-rules', [TenantShippingRuleController::class, 'store'])->name('tenant.shipping-rules.store');
        Route::get('commerce-features/{feature}', [TenantCommerceFeatureController::class, 'show'])->name('tenant.commerce-feature');
        Route::post('commerce-features/{feature}', [TenantCommerceFeatureController::class, 'update'])->name('tenant.commerce-feature.update');
        Route::get('commerce-features/{feature}/export-products', [TenantCommerceFeatureController::class, 'exportProducts'])->name('tenant.commerce-feature.export-products');
        Route::post('commerce-features/bulk_import_export/import-products', [TenantCommerceFeatureController::class, 'importProducts'])->name('tenant.commerce-feature.import-products');

        // Orders
        Route::get('orders', [TenantOrderController::class, 'index'])->name('tenant.orders');
        Route::post('orders/{id}/status', [TenantOrderController::class, 'updateStatus'])->name('tenant.orders.update-status');
        Route::get('orders/{id}/invoice', [TenantOrderController::class, 'invoice'])->name('tenant.orders.invoice');

        // Reservations (restaurant tenants)
        Route::get('reservations', [TenantReservationController::class, 'index'])->name('tenant.reservations');
        Route::post('reservations/{id}/status', [TenantReservationController::class, 'updateStatus'])->name('tenant.reservations.update-status');

        // Analytics (gated by the analytics_pro add-on inside the controller)
        Route::get('analytics', [\App\Http\Controllers\Tenant\TenantAnalyticsController::class, 'index'])->name('tenant.analytics');

        // Services (portfolio/business tenants)
        Route::get('services', [TenantServiceController::class, 'index'])->name('tenant.services');
        Route::get('services/create', [TenantServiceController::class, 'create'])->name('tenant.services.create');
        Route::post('services', [TenantServiceController::class, 'store'])->name('tenant.services.store');
        Route::get('services/{id}/edit', [TenantServiceController::class, 'edit'])->name('tenant.services.edit');
        Route::put('services/{id}', [TenantServiceController::class, 'update'])->name('tenant.services.update');
        Route::delete('services/{id}', [TenantServiceController::class, 'destroy'])->name('tenant.services.destroy');

        // Testimonials (portfolio/business tenants)
        Route::get('testimonials', [TenantTestimonialController::class, 'index'])->name('tenant.testimonials');
        Route::get('testimonials/create', [TenantTestimonialController::class, 'create'])->name('tenant.testimonials.create');
        Route::post('testimonials', [TenantTestimonialController::class, 'store'])->name('tenant.testimonials.store');
        Route::get('testimonials/{id}/edit', [TenantTestimonialController::class, 'edit'])->name('tenant.testimonials.edit');
        Route::put('testimonials/{id}', [TenantTestimonialController::class, 'update'])->name('tenant.testimonials.update');
        Route::delete('testimonials/{id}', [TenantTestimonialController::class, 'destroy'])->name('tenant.testimonials.destroy');

        // Blog (portfolio/business tenants)
        Route::get('blog', [TenantBlogController::class, 'index'])->name('tenant.blog');
        Route::get('blog/create', [TenantBlogController::class, 'create'])->name('tenant.blog.create');
        Route::post('blog', [TenantBlogController::class, 'store'])->name('tenant.blog.store');
        Route::get('blog/{id}/edit', [TenantBlogController::class, 'edit'])->name('tenant.blog.edit');
        Route::put('blog/{id}', [TenantBlogController::class, 'update'])->name('tenant.blog.update');
        Route::delete('blog/{id}', [TenantBlogController::class, 'destroy'])->name('tenant.blog.destroy');

        Route::get('business-content/{type}', [TenantBusinessContentController::class, 'index'])->name('tenant.business-content.index');
        Route::get('business-content/{type}/create', [TenantBusinessContentController::class, 'create'])->name('tenant.business-content.create');
        Route::post('business-content/{type}', [TenantBusinessContentController::class, 'store'])->name('tenant.business-content.store');
        Route::get('business-content/{type}/{id}/edit', [TenantBusinessContentController::class, 'edit'])->name('tenant.business-content.edit');
        Route::put('business-content/{type}/{id}', [TenantBusinessContentController::class, 'update'])->name('tenant.business-content.update');
        Route::delete('business-content/{type}/{id}', [TenantBusinessContentController::class, 'destroy'])->name('tenant.business-content.destroy');
        Route::get('business-inbox', [TenantBusinessInboxController::class, 'index'])->name('tenant.business-inbox');
        Route::patch('business-inbox/{id}', [TenantBusinessInboxController::class, 'update'])->name('tenant.business-inbox.update');
        Route::delete('business-inbox/{id}', [TenantBusinessInboxController::class, 'destroy'])->name('tenant.business-inbox.destroy');

        // Theme customizer
        Route::get('customize', [TenantThemeController::class, 'edit'])->name('tenant.theme');
        Route::post('customize', [TenantThemeController::class, 'update'])->name('tenant.theme.update');

        // Add-on marketplace
        Route::get('addons', [\App\Http\Controllers\Tenant\TenantAddonController::class, 'index'])->name('tenant.addons');
        Route::post('addons/toggle/{addonKey}', [\App\Http\Controllers\Tenant\TenantAddonController::class, 'toggle'])->name('tenant.addons.toggle');
    });

    // Support tickets
    Route::middleware('tenant.auth')->group(function () {
        Route::get('support', [TenantTicketController::class, 'index'])->name('tenant.tickets');
        Route::post('support', [TenantTicketController::class, 'store'])->name('tenant.tickets.store');
        Route::get('support/{ticket}', [TenantTicketController::class, 'show'])->name('tenant.tickets.show');
    });

    // Logout (POST, no auth middleware since session is still valid)
    Route::post('logout', [TenantLoginController::class, 'destroy'])->name('tenant.logout');
});

// Impersonation leave — must be reachable while logged in as a tenant user
// (no tenant.auth middleware, but still requires TenantContext).
Route::middleware('tenant')->post('leave-impersonation', [TenantImpersonateController::class, 'leave'])->name('tenant.leave-impersonation');

}); // end Route::domain('{subdomain}.'.config('app.tenant_domain'))
