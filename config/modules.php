<?php

return [
    'content' => [
        'label' => 'Content / Pages',
        'icon' => 'ti-file-text',
        'nav_section' => 'Content',
        'route' => 'tenant.content',
        'description' => 'About text, gallery images, and contact details.',
    ],
    'catalog' => [
        'label' => 'Catalog',
        'icon' => 'ti-box',
        'nav_section' => 'Store',
        'route' => 'tenant.catalog',
        'description' => 'Product catalog with photos, pricing, and buy buttons.',
    ],
    'payments' => [
        'label' => 'Payment Settings',
        'icon' => 'ti-credit-card',
        'nav_section' => 'Store',
        'route' => 'tenant.payments',
        'description' => 'Razorpay key/secret configuration.',
    ],
    'orders' => [
        'label' => 'Orders',
        'icon' => 'ti-truck-delivery',
        'nav_section' => 'Store',
        'route' => 'tenant.orders',
        'description' => 'View incoming orders and update status.',
    ],
    'reservations' => [
        'label' => 'Reservations',
        'icon' => 'ti-calendar-event',
        'nav_section' => 'Store',
        'route' => 'tenant.reservations',
        'description' => 'Table booking requests from the storefront.',
    ],
    'services' => [
        'label' => 'Services',
        'icon' => 'ti-briefcase-2',
        'nav_section' => 'Business',
        'route' => 'tenant.services',
        'description' => 'Services you offer, with optional pricing.',
    ],
    'testimonials' => [
        'label' => 'Testimonials',
        'icon' => 'ti-quote',
        'nav_section' => 'Business',
        'route' => 'tenant.testimonials',
        'description' => 'Client quotes and reviews shown on your site.',
    ],
    'blog' => [
        'label' => 'Blog',
        'icon' => 'ti-news',
        'nav_section' => 'Business',
        'route' => 'tenant.blog',
        'description' => 'Articles and news posts for your site.',
    ],
];
