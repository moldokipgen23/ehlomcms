<?php

/**
 * Feature Bundles — defines what features each business type gets
 * at each tier (free / pro / premium).
 *
 * Free = included in all sites of this type, no extra cost.
 * Pro = paid monthly add-on, toggled per tenant.
 * Premium = higher-tier paid add-on, future features.
 *
 * The 'modules' key below keeps the old format for backward
 * compatibility with Tenant::hasModule() checks.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Bundles per Business Type
    |--------------------------------------------------------------------------
    */
    'bundles' => [

        'school' => [
            'label' => 'School',
            'icon' => 'ti-school',
            'description' => 'Professional school website with all essential sections included free.',
            'free' => [
                ['key' => 'hero', 'name' => 'Hero Banner', 'icon' => 'ti-photo', 'description' => 'Full-width banner with school name, motto, and admission call-to-action.'],
                ['key' => 'about', 'name' => 'About School', 'icon' => 'ti-info-circle', 'description' => 'School history, vision, mission, core values, and principal message.'],
                ['key' => 'academics', 'name' => 'Academics', 'icon' => 'ti-book', 'description' => 'Curriculum, classes, subjects, school timings, and examination system.'],
                ['key' => 'admissions', 'name' => 'Admissions', 'icon' => 'tiClipboard', 'description' => 'Admission process, eligibility, fee structure, and required documents.'],
                ['key' => 'faculty', 'name' => 'Faculty & Staff', 'icon' => 'ti-users', 'description' => 'Principal, teachers, and staff profiles with photos and qualifications.'],
                ['key' => 'student_life', 'name' => 'Student Life', 'icon' => 'ti-mood-happy', 'description' => 'Clubs, sports, cultural activities, competitions, and events.'],
                ['key' => 'gallery', 'name' => 'Gallery', 'icon' => 'ti-photo', 'description' => 'Photo albums — campus, classrooms, events, and activities.'],
                ['key' => 'news', 'name' => 'News & Events', 'icon' => 'ti-news', 'description' => 'School news, circulars, holiday notices, and upcoming events.'],
                ['key' => 'contact', 'name' => 'Contact', 'icon' => 'ti-map-pin', 'description' => 'Address, phone, email, office hours, and WhatsApp button.'],
                ['key' => 'footer', 'name' => 'Footer', 'icon' => 'ti-layout-sidebar', 'description' => 'Quick links, important links, social media, and copyright.'],
            ],
            'pro' => [
                ['key' => 'stats', 'name' => 'School Statistics', 'icon' => 'ti-chart-bar', 'description' => 'Animated counters — years, students, teachers, classrooms, awards.', 'price' => 499],
                ['key' => 'why_choose', 'name' => 'Why Choose Us', 'icon' => 'ti-star', 'description' => '6 reason cards highlighting school strengths.', 'price' => 499],
                ['key' => 'achievements', 'name' => 'Achievements', 'icon' => 'ti-trophy', 'description' => 'Student toppers, sports winners, board results, awards.', 'price' => 499],
                ['key' => 'testimonials', 'name' => 'Testimonials', 'icon' => 'ti-quote', 'description' => 'Parent and student reviews with ratings.', 'price' => 499],
                ['key' => 'downloads', 'name' => 'Downloads', 'icon' => 'ti-file-download', 'description' => 'Admission forms, fee structure, prospectus, calendar, circulars.', 'price' => 499],
                ['key' => 'certificates', 'name' => 'Certificates & Recognition', 'icon' => 'ti-certificate', 'description' => 'Government recognition, affiliation, safety certificates.', 'price' => 499],
                ['key' => 'map', 'name' => 'Google Map', 'icon' => 'ti-map', 'description' => 'Embedded Google Map on contact section.', 'price' => 299],
                ['key' => 'enquiry_form', 'name' => 'Online Enquiry Form', 'icon' => 'ti-edit', 'description' => 'Admission enquiry form with email notifications.', 'price' => 799],
            ],
            'premium' => [
                ['key' => 'admission_form', 'name' => 'Online Admission Form', 'icon' => 'tiForms', 'description' => 'Full admission form with document upload and payment.', 'price' => 1999],
                ['key' => 'fee_calculator', 'name' => 'Fee Calculator', 'icon' => 'ti-calculator', 'description' => 'Interactive fee breakdown by class and stream.', 'price' => 1499],
                ['key' => 'student_portal', 'name' => 'Student Portal', 'icon' => 'ti-user', 'description' => 'Student login with results, attendance, and homework.', 'price' => 2999, 'future' => true],
                ['key' => 'parent_login', 'name' => 'Parent Login', 'icon' => 'ti-users', 'description' => 'Parent dashboard with child progress and notifications.', 'price' => 2999, 'future' => true],
                ['key' => 'erp', 'name' => 'ERP Integration', 'icon' => 'ti-device-desktop', 'description' => 'Full school ERP — attendance, timetable, exams, fees.', 'price' => 4999, 'future' => true],
            ],
        ],

        'shopping' => [
            'label' => 'Shopping / Store',
            'icon' => 'ti-shopping-cart',
            'description' => 'Full e-commerce storefront with catalog, cart, and checkout.',
            'free' => [
                ['key' => 'catalog', 'name' => 'Product Catalog', 'icon' => 'ti-box', 'description' => 'Product listings with photos, pricing, and descriptions.'],
                ['key' => 'cart', 'name' => 'Shopping Cart', 'icon' => 'ti-shopping-cart', 'description' => 'Session-based cart with quantity management.'],
                ['key' => 'checkout', 'name' => 'Checkout', 'icon' => 'ti-credit-card', 'description' => 'Razorpay / COD / Custom gateway checkout flow.'],
                ['key' => 'orders', 'name' => 'Order Management', 'icon' => 'ti-truck-delivery', 'description' => 'View orders, update status, tracking.'],
                ['key' => 'payments', 'name' => 'Payment Settings', 'icon' => 'ti-credit-card', 'description' => 'Razorpay, Stripe, PayPal, or custom gateway config.'],
                ['key' => 'content', 'name' => 'Content Pages', 'icon' => 'ti-file-text', 'description' => 'About text, gallery images, and contact details.'],
            ],
            'pro' => [
                ['key' => 'wishlist', 'name' => 'Wishlist', 'icon' => 'ti-heart', 'description' => 'Save products for later with wishlist feature.', 'price' => 799],
                ['key' => 'filters', 'name' => 'Product Filters', 'icon' => 'ti-filter', 'description' => 'Filter by category, price range, size, color.', 'price' => 999],
                ['key' => 'reviews', 'name' => 'Product Reviews', 'icon' => 'ti-star', 'description' => 'Customer ratings and reviews on product pages.', 'price' => 799],
                ['key' => 'coupons', 'name' => 'Coupons & Discounts', 'icon' => 'ti-ticket', 'description' => 'Create discount codes and promotional offers.', 'price' => 1299],
            ],
            'premium' => [
                ['key' => 'multi_vendor', 'name' => 'Multi-Vendor', 'icon' => 'ti-building-store', 'description' => 'Multiple sellers with independent dashboards.', 'price' => 4999, 'future' => true],
                ['key' => 'subscription', 'name' => 'Subscription Billing', 'icon' => 'ti-repeat', 'description' => 'Recurring payments and subscription products.', 'price' => 3999],
                ['key' => 'pos', 'name' => 'POS Integration', 'icon' => 'ti-device-desktop', 'description' => 'Sync with Square, Toast, Clover POS systems.', 'price' => 4999, 'future' => true],
            ],
        ],

        'restaurant' => [
            'label' => 'Restaurant',
            'icon' => 'ti-utensils',
            'description' => 'Menu-focused site with online ordering and table reservations.',
            'free' => [
                ['key' => 'menu', 'name' => 'Menu / Catalog', 'icon' => 'ti-book', 'description' => 'Menu items with photos, prices, and categories.'],
                ['key' => 'reservations', 'name' => 'Table Reservations', 'icon' => 'ti-calendar-event', 'description' => 'Online table booking requests from storefront.'],
                ['key' => 'orders', 'name' => 'Orders', 'icon' => 'ti-truck-delivery', 'description' => 'Incoming orders with status management.'],
                ['key' => 'payments', 'name' => 'Payment Settings', 'icon' => 'ti-credit-card', 'description' => 'Razorpay, COD, or custom gateway config.'],
                ['key' => 'gallery', 'name' => 'Gallery', 'icon' => 'ti-photo', 'description' => 'Food photos, ambiance, and event images.'],
                ['key' => 'content', 'name' => 'Content Pages', 'icon' => 'ti-file-text', 'description' => 'About, contact details, and opening hours.'],
            ],
            'pro' => [
                ['key' => 'online_ordering', 'name' => 'Online Ordering', 'icon' => 'ti-shopping-cart', 'description' => 'Full food ordering with cart and delivery options.', 'price' => 1499],
                ['key' => 'table_booking', 'name' => 'Advanced Table Booking', 'icon' => 'ti-calendar', 'description' => 'Time slots, party size, special requests.', 'price' => 999],
                ['key' => 'events', 'name' => 'Events & Offers', 'icon' => 'ti-speakerphone', 'description' => 'Promote events, happy hours, and special offers.', 'price' => 799],
            ],
            'premium' => [
                ['key' => 'delivery', 'name' => 'Delivery Tracking', 'icon' => 'ti-route', 'description' => 'Real-time order tracking for customers.', 'price' => 2999, 'future' => true],
                ['key' => 'loyalty', 'name' => 'Loyalty Program', 'icon' => 'ti-gift', 'description' => 'Points, rewards, and referral system.', 'price' => 2999],
                ['key' => 'multi_branch', 'name' => 'Multi-Branch', 'icon' => 'ti-map-pin', 'description' => 'Manage multiple locations with centralized menu.', 'price' => 4999, 'future' => true],
            ],
        ],

        'business' => [
            'label' => 'Portfolio / Business',
            'icon' => 'ti-briefcase-2',
            'description' => 'Professional site with services, testimonials, and blog.',
            'free' => [
                ['key' => 'services', 'name' => 'Services', 'icon' => 'ti-briefcase-2', 'description' => 'Service listings with descriptions and pricing.'],
                ['key' => 'testimonials', 'name' => 'Testimonials', 'icon' => 'ti-quote', 'description' => 'Client quotes and reviews with ratings.'],
                ['key' => 'blog', 'name' => 'Blog', 'icon' => 'ti-news', 'description' => 'Articles and news posts for SEO and updates.'],
                ['key' => 'content', 'name' => 'Content Pages', 'icon' => 'ti-file-text', 'description' => 'About text, gallery images, and contact details.'],
            ],
            'pro' => [
                ['key' => 'case_studies', 'name' => 'Case Studies', 'icon' => 'ti-file-text', 'description' => 'Detailed project showcases with results.', 'price' => 999],
                ['key' => 'team', 'name' => 'Team Profiles', 'icon' => 'ti-users', 'description' => 'Team grid with roles and bios.', 'price' => 799],
                ['key' => 'careers', 'name' => 'Careers', 'icon' => 'ti-badge', 'description' => 'Job listings and application form.', 'price' => 999],
                ['key' => 'newsletter', 'name' => 'Newsletter Signup', 'icon' => 'ti-mail', 'description' => 'Email capture form for marketing.', 'price' => 499],
            ],
            'premium' => [
                ['key' => 'client_portal', 'name' => 'Client Portal', 'icon' => 'ti-login', 'description' => 'Client login with project tracking.', 'price' => 3999, 'future' => true],
                ['key' => 'project_mgmt', 'name' => 'Project Management', 'icon' => 'ti-list', 'description' => 'Task boards and project timelines.', 'price' => 4999, 'future' => true],
                ['key' => 'crm', 'name' => 'CRM', 'icon' => 'ti-users', 'description' => 'Lead tracking and customer management.', 'price' => 4999, 'future' => true],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Keys (backward compatibility with Tenant::hasModule)
    |--------------------------------------------------------------------------
    | Maps module keys to their routes and labels for dashboard gating.
    */
    'content' => [
        'label' => 'Content / Pages',
        'icon' => 'ti-file-text',
        'nav_section' => 'Content',
        'route' => 'tenant.content',
        'description' => 'About text, gallery images, and contact details.',
        'price' => 0,
        'free' => true,
        'business_types' => ['school', 'shopping', 'restaurant', 'business'],
    ],
    'catalog' => [
        'label' => 'Product Catalog',
        'icon' => 'ti-box',
        'nav_section' => 'Store',
        'route' => 'tenant.catalog',
        'description' => 'Product catalog with photos, pricing, and buy buttons.',
        'price' => 0,
        'free' => true,
        'business_types' => ['shopping', 'restaurant'],
    ],
    'payments' => [
        'label' => 'Payment Settings',
        'icon' => 'ti-credit-card',
        'nav_section' => 'Store',
        'route' => 'tenant.payments',
        'description' => 'Razorpay / Stripe / Custom gateway configuration.',
        'price' => 0,
        'free' => true,
        'business_types' => ['shopping', 'restaurant'],
    ],
    'orders' => [
        'label' => 'Orders',
        'icon' => 'ti-truck-delivery',
        'nav_section' => 'Store',
        'route' => 'tenant.orders',
        'description' => 'View incoming orders and update status.',
        'price' => 0,
        'free' => true,
        'business_types' => ['shopping', 'restaurant'],
    ],
    'reservations' => [
        'label' => 'Reservations',
        'icon' => 'ti-calendar-event',
        'nav_section' => 'Store',
        'route' => 'tenant.reservations',
        'description' => 'Table booking requests from the storefront.',
        'price' => 0,
        'free' => true,
        'business_types' => ['restaurant'],
    ],
    'services' => [
        'label' => 'Services',
        'icon' => 'ti-briefcase-2',
        'nav_section' => 'Business',
        'route' => 'tenant.services',
        'description' => 'Services you offer, with optional pricing.',
        'price' => 0,
        'free' => true,
        'business_types' => ['business'],
    ],
    'testimonials' => [
        'label' => 'Testimonials',
        'icon' => 'ti-quote',
        'nav_section' => 'Business',
        'route' => 'tenant.testimonials',
        'description' => 'Client quotes and reviews shown on your site.',
        'price' => 0,
        'free' => true,
        'business_types' => ['business'],
    ],
    'blog' => [
        'label' => 'Blog',
        'icon' => 'ti-news',
        'nav_section' => 'Business',
        'route' => 'tenant.blog',
        'description' => 'Articles and news posts for your site.',
        'price' => 0,
        'free' => true,
        'business_types' => ['business'],
    ],
    'analytics_pro' => [
        'label' => 'Analytics Pro',
        'icon' => 'ti-chart-bar',
        'nav_section' => 'Business',
        'route' => 'tenant.analytics',
        'description' => 'Advanced analytics: heatmaps, funnels, cohort analysis, custom reports.',
        'price' => 1999,
        'free' => false,
        'business_types' => ['school', 'shopping', 'restaurant', 'business'],
    ],
];
