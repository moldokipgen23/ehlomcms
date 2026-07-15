<?php

/**
 * Business Feature Bundles — defines what features each business type gets.
 *
 * Each feature has:
 *   toggleable = true  → admin can toggle on/off per tenant
 *   price > 0          → paid add-on
 *   future = true      → not built yet, shows "Coming Soon"
 */

return [

    'bundles' => [

        'school' => [
            'label' => 'School',
            'icon' => 'ti-school',
            'description' => 'Professional school website with all essential sections included free.',
            'free' => [
                ['key' => 'hero', 'name' => 'Hero Banner', 'icon' => 'ti-photo', 'description' => 'Full-width banner with school name, motto, and admission CTA.', 'toggleable' => true, 'price' => 0],
                ['key' => 'stats', 'name' => 'School Highlights', 'icon' => 'ti-star', 'description' => 'Quick highlight cards — Experienced Faculty, Quality Education, etc.', 'toggleable' => true, 'price' => 0],
                ['key' => 'about', 'name' => 'About School', 'icon' => 'ti-info-circle', 'description' => 'School history, vision, mission, core values, and principal message.', 'toggleable' => true, 'price' => 0],
                ['key' => 'academics', 'name' => 'Academics', 'icon' => 'ti-book', 'description' => 'Curriculum, classes, subjects, school timings, and examination system.', 'toggleable' => true, 'price' => 0],
                ['key' => 'admissions', 'name' => 'Admissions', 'icon' => 'ti-clipboard', 'description' => 'Admission process, eligibility, fee structure, documents, FAQs.', 'toggleable' => true, 'price' => 0],
                ['key' => 'faculty', 'name' => 'Faculty & Staff', 'icon' => 'ti-users', 'description' => 'Principal, teachers, and staff profiles with photos and qualifications.', 'toggleable' => true, 'price' => 0],
                ['key' => 'student_life', 'name' => 'Student Life', 'icon' => 'ti-mood-happy', 'description' => 'Clubs, sports, cultural activities, competitions, and events.', 'toggleable' => true, 'price' => 0],
                ['key' => 'facilities', 'name' => 'Facilities', 'icon' => 'ti-building', 'description' => 'Smart classrooms, library, computer lab, science lab, playground, transport.', 'toggleable' => true, 'price' => 0],
                ['key' => 'gallery', 'name' => 'Gallery', 'icon' => 'ti-photo', 'description' => 'Photo albums — campus, classrooms, events, sports, cultural.', 'toggleable' => true, 'price' => 0],
                ['key' => 'news', 'name' => 'News & Events', 'icon' => 'ti-news', 'description' => 'School news, circulars, holiday notices, and upcoming events.', 'toggleable' => true, 'price' => 0],
                ['key' => 'achievements', 'name' => 'Achievements', 'icon' => 'ti-trophy', 'description' => 'Student toppers, sports winners, board results, awards.', 'toggleable' => true, 'price' => 0],
                ['key' => 'testimonials', 'name' => 'Testimonials', 'icon' => 'ti-quote', 'description' => 'Parent and student reviews with ratings.', 'toggleable' => true, 'price' => 0],
                ['key' => 'downloads', 'name' => 'Downloads', 'icon' => 'ti-file-download', 'description' => 'Admission forms, fee structure, prospectus, calendar, circulars.', 'toggleable' => true, 'price' => 0],
                ['key' => 'certificates', 'name' => 'Certificates & Recognition', 'icon' => 'ti-certificate', 'description' => 'Government recognition, affiliation, safety certificates.', 'toggleable' => true, 'price' => 0],
                ['key' => 'contact', 'name' => 'Contact', 'icon' => 'ti-map-pin', 'description' => 'Address, phone, email, office hours, Google Map, WhatsApp button.', 'toggleable' => true, 'price' => 0],
            ],
            'pro' => [
                ['key' => 'why_choose', 'name' => 'Why Choose Us', 'icon' => 'ti-star', 'description' => '6 reason cards highlighting school strengths.', 'toggleable' => true, 'price' => 499],
                ['key' => 'map', 'name' => 'Google Map Embed', 'icon' => 'ti-map', 'description' => 'Embedded Google Map on contact section.', 'toggleable' => true, 'price' => 299],
                ['key' => 'enquiry_form', 'name' => 'Online Enquiry Form', 'icon' => 'ti-edit', 'description' => 'Admission enquiry form with email notifications.', 'toggleable' => true, 'price' => 799],
            ],
            'premium' => [
                ['key' => 'admission_form', 'name' => 'Online Admission Form', 'icon' => 'ti-forms', 'description' => 'Full admission form with document upload and payment.', 'toggleable' => false, 'price' => 1999, 'future' => true],
                ['key' => 'fee_calculator', 'name' => 'Fee Calculator', 'icon' => 'ti-calculator', 'description' => 'Interactive fee breakdown by class and stream.', 'toggleable' => false, 'price' => 1499, 'future' => true],
                ['key' => 'student_portal', 'name' => 'Student Portal', 'icon' => 'ti-user', 'description' => 'Student login with results, attendance, and homework.', 'toggleable' => false, 'price' => 2999, 'future' => true],
                ['key' => 'parent_login', 'name' => 'Parent Login', 'icon' => 'ti-users', 'description' => 'Parent dashboard with child progress and notifications.', 'toggleable' => false, 'price' => 2999, 'future' => true],
                ['key' => 'erp', 'name' => 'ERP Integration', 'icon' => 'ti-device-desktop', 'description' => 'Full school ERP — attendance, timetable, exams, fees.', 'toggleable' => false, 'price' => 4999, 'future' => true],
            ],
        ],

        'shopping' => [
            'label' => 'Shopping / Store',
            'icon' => 'ti-shopping-cart',
            'description' => 'Full e-commerce storefront with catalog, cart, and checkout.',
            'free' => [
                ['key' => 'catalog', 'name' => 'Product Catalog', 'icon' => 'ti-box', 'description' => 'Product listings with photos, pricing, and descriptions.', 'toggleable' => true, 'price' => 0],
                ['key' => 'cart', 'name' => 'Shopping Cart', 'icon' => 'ti-shopping-cart', 'description' => 'Session-based cart with quantity management.', 'toggleable' => true, 'price' => 0],
                ['key' => 'checkout', 'name' => 'Checkout', 'icon' => 'ti-credit-card', 'description' => 'Razorpay / COD / Custom gateway checkout flow.', 'toggleable' => true, 'price' => 0],
                ['key' => 'orders', 'name' => 'Order Management', 'icon' => 'ti-truck-delivery', 'description' => 'View orders, update status, tracking.', 'toggleable' => true, 'price' => 0],
                ['key' => 'payments', 'name' => 'Payment Settings', 'icon' => 'ti-credit-card', 'description' => 'Razorpay, Stripe, PayPal, or custom gateway config.', 'toggleable' => true, 'price' => 0],
                ['key' => 'content', 'name' => 'Content Pages', 'icon' => 'ti-file-text', 'description' => 'About text, gallery images, and contact details.', 'toggleable' => true, 'price' => 0],
            ],
            'pro' => [
                ['key' => 'wishlist', 'name' => 'Wishlist', 'icon' => 'ti-heart', 'description' => 'Save products for later with wishlist feature.', 'toggleable' => true, 'price' => 799],
                ['key' => 'filters', 'name' => 'Product Filters', 'icon' => 'ti-filter', 'description' => 'Filter by category, price range, size, color.', 'toggleable' => true, 'price' => 999],
                ['key' => 'reviews', 'name' => 'Product Reviews', 'icon' => 'ti-star', 'description' => 'Customer ratings and reviews on product pages.', 'toggleable' => true, 'price' => 799],
                ['key' => 'coupons', 'name' => 'Coupons & Discounts', 'icon' => 'ti-ticket', 'description' => 'Create discount codes and promotional offers.', 'toggleable' => true, 'price' => 1299],
            ],
            'premium' => [
                ['key' => 'multi_vendor', 'name' => 'Multi-Vendor', 'icon' => 'ti-building-store', 'description' => 'Multiple sellers with independent dashboards.', 'toggleable' => false, 'price' => 4999, 'future' => true],
                ['key' => 'subscription', 'name' => 'Subscription Billing', 'icon' => 'ti-repeat', 'description' => 'Recurring payments and subscription products.', 'toggleable' => false, 'price' => 3999],
                ['key' => 'pos', 'name' => 'POS Integration', 'icon' => 'ti-device-desktop', 'description' => 'Sync with Square, Toast, Clover POS systems.', 'toggleable' => false, 'price' => 4999, 'future' => true],
            ],
        ],

        'restaurant' => [
            'label' => 'Restaurant',
            'icon' => 'ti-utensils',
            'description' => 'Menu-focused site with online ordering and table reservations.',
            'free' => [
                ['key' => 'menu', 'name' => 'Menu / Catalog', 'icon' => 'ti-book', 'description' => 'Menu items with photos, prices, and categories.', 'toggleable' => true, 'price' => 0],
                ['key' => 'reservations', 'name' => 'Table Reservations', 'icon' => 'ti-calendar-event', 'description' => 'Online table booking requests from storefront.', 'toggleable' => true, 'price' => 0],
                ['key' => 'orders', 'name' => 'Orders', 'icon' => 'ti-truck-delivery', 'description' => 'Incoming orders with status management.', 'toggleable' => true, 'price' => 0],
                ['key' => 'payments', 'name' => 'Payment Settings', 'icon' => 'ti-credit-card', 'description' => 'Razorpay, COD, or custom gateway config.', 'toggleable' => true, 'price' => 0],
                ['key' => 'gallery', 'name' => 'Gallery', 'icon' => 'ti-photo', 'description' => 'Food photos, ambiance, and event images.', 'toggleable' => true, 'price' => 0],
                ['key' => 'content', 'name' => 'Content Pages', 'icon' => 'ti-file-text', 'description' => 'About, contact details, and opening hours.', 'toggleable' => true, 'price' => 0],
            ],
            'pro' => [
                ['key' => 'online_ordering', 'name' => 'Online Ordering', 'icon' => 'ti-shopping-cart', 'description' => 'Full food ordering with cart and delivery options.', 'toggleable' => true, 'price' => 1499],
                ['key' => 'table_booking', 'name' => 'Advanced Table Booking', 'icon' => 'ti-calendar', 'description' => 'Time slots, party size, special requests.', 'toggleable' => true, 'price' => 999],
                ['key' => 'events', 'name' => 'Events & Offers', 'icon' => 'ti-speakerphone', 'description' => 'Promote events, happy hours, and special offers.', 'toggleable' => true, 'price' => 799],
            ],
            'premium' => [
                ['key' => 'delivery', 'name' => 'Delivery Tracking', 'icon' => 'ti-route', 'description' => 'Real-time order tracking for customers.', 'toggleable' => false, 'price' => 2999, 'future' => true],
                ['key' => 'loyalty', 'name' => 'Loyalty Program', 'icon' => 'ti-gift', 'description' => 'Points, rewards, and referral system.', 'toggleable' => false, 'price' => 2999],
                ['key' => 'multi_branch', 'name' => 'Multi-Branch', 'icon' => 'ti-map-pin', 'description' => 'Manage multiple locations with centralized menu.', 'toggleable' => false, 'price' => 4999, 'future' => true],
            ],
        ],

        'business' => [
            'label' => 'Portfolio / Business',
            'icon' => 'ti-briefcase-2',
            'description' => 'Professional site with services, testimonials, and blog.',
            'free' => [
                ['key' => 'services', 'name' => 'Services', 'icon' => 'ti-briefcase-2', 'description' => 'Service listings with descriptions and pricing.', 'toggleable' => true, 'price' => 0],
                ['key' => 'testimonials', 'name' => 'Testimonials', 'icon' => 'ti-quote', 'description' => 'Client quotes and reviews with ratings.', 'toggleable' => true, 'price' => 0],
                ['key' => 'blog', 'name' => 'Blog', 'icon' => 'ti-news', 'description' => 'Articles and news posts for SEO and updates.', 'toggleable' => true, 'price' => 0],
                ['key' => 'content', 'name' => 'Content Pages', 'icon' => 'ti-file-text', 'description' => 'About text, gallery images, and contact details.', 'toggleable' => true, 'price' => 0],
            ],
            'pro' => [
                ['key' => 'case_studies', 'name' => 'Case Studies', 'icon' => 'ti-file-text', 'description' => 'Detailed project showcases with results.', 'toggleable' => true, 'price' => 999],
                ['key' => 'team', 'name' => 'Team Profiles', 'icon' => 'ti-users', 'description' => 'Team grid with roles and bios.', 'toggleable' => true, 'price' => 799],
                ['key' => 'careers', 'name' => 'Careers', 'icon' => 'ti-badge', 'description' => 'Job listings and application form.', 'toggleable' => true, 'price' => 999],
                ['key' => 'newsletter', 'name' => 'Newsletter Signup', 'icon' => 'ti-mail', 'description' => 'Email capture form for marketing.', 'toggleable' => true, 'price' => 499],
            ],
            'premium' => [
                ['key' => 'client_portal', 'name' => 'Client Portal', 'icon' => 'ti-login', 'description' => 'Client login with project tracking.', 'toggleable' => false, 'price' => 3999, 'future' => true],
                ['key' => 'project_mgmt', 'name' => 'Project Management', 'icon' => 'ti-list', 'description' => 'Task boards and project timelines.', 'toggleable' => false, 'price' => 4999, 'future' => true],
                ['key' => 'crm', 'name' => 'CRM', 'icon' => 'ti-users', 'description' => 'Lead tracking and customer management.', 'toggleable' => false, 'price' => 4999, 'future' => true],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Keys (backward compatibility with Tenant::hasModule)
    |--------------------------------------------------------------------------
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
        'description' => 'Advanced analytics: heatmaps, funnels, cohort analysis.',
        'price' => 1999,
        'free' => false,
        'business_types' => ['school', 'shopping', 'restaurant', 'business'],
    ],
];
