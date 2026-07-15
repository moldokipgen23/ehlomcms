<?php

return [
    'school' => [
        'label' => 'School',
        'folder' => 'school',
        'themes' => [
            'classic' => [
                'name' => 'School Classic',
                'description' => 'Professional school website with all sections — hero, principal message, stats, academics, admissions, faculty, events, gallery, news, achievements, and contact.',
                'thumbnail' => 'images/themes/school/classic-preview.png',
                'public' => true,
                'free' => true,
            ],
            'modern' => [
                'name' => 'School Modern',
                'description' => 'Modern school website with animated sections, video backgrounds, and interactive campus tour.',
                'thumbnail' => 'images/themes/school/modern-preview.png',
                'public' => true,
                'free' => false,
                'price' => 1999,
            ],
        ],
    ],

    'shopping' => [
        'label' => 'Shopping / Store',
        'folder' => 'shopping',
        'themes' => [
            'classic' => [
                'name' => 'Shop Classic',
                'description' => 'Full storefront with product catalog, pricing, and buy buttons. Great for e-commerce and retail.',
                'thumbnail' => 'images/themes/shopping/classic-preview.png',
                'public' => true,
                'free' => true,
            ],
            'premium' => [
                'name' => 'Shop Premium',
                'description' => 'Advanced storefront with quick view, wishlist, product filters, and multi-currency.',
                'thumbnail' => 'images/themes/shopping/premium-preview.png',
                'public' => true,
                'free' => false,
                'price' => 2999,
            ],
            'minimal' => [
                'name' => 'Shop Minimal',
                'description' => 'Clean minimalist design focusing on product imagery and fast checkout.',
                'thumbnail' => 'images/themes/shopping/minimal-preview.png',
                'public' => true,
                'free' => false,
                'price' => 1999,
            ],
        ],
    ],

    'restaurant' => [
        'label' => 'Restaurant',
        'folder' => 'restaurant',
        'themes' => [
            'classic' => [
                'name' => 'Restaurant Classic',
                'description' => 'Menu-focused layout with online ordering, table reservations, and gallery.',
                'thumbnail' => 'images/themes/restaurant/classic-preview.png',
                'public' => true,
                'free' => true,
            ],
            'fine-dine' => [
                'name' => 'Fine Dine',
                'description' => 'Elegant design for upscale restaurants with wine list, chef profiles, and events.',
                'thumbnail' => 'images/themes/restaurant/fine-dine-preview.png',
                'public' => true,
                'free' => false,
                'price' => 2499,
            ],
        ],
    ],

    'business' => [
        'label' => 'Portfolio / Business',
        'folder' => 'business',
        'themes' => [
            'classic' => [
                'name' => 'Business Classic',
                'description' => 'Professional portfolio with services, testimonials, blog, and contact form.',
                'thumbnail' => 'images/themes/business/classic-preview.png',
                'public' => true,
                'free' => true,
            ],
            'agency' => [
                'name' => 'Agency Pro',
                'description' => 'Agency-focused with case studies, team grid, client logos, and lead capture.',
                'thumbnail' => 'images/themes/business/agency-preview.png',
                'public' => true,
                'free' => false,
                'price' => 3499,
            ],
            'freelancer' => [
                'name' => 'Freelancer',
                'description' => 'Personal portfolio with skills, projects, availability calendar, and booking.',
                'thumbnail' => 'images/themes/business/freelancer-preview.png',
                'public' => true,
                'free' => false,
                'price' => 1499,
            ],
            'corporate' => [
                'name' => 'Corporate',
                'description' => 'Large business layout with departments, careers, newsroom, and investor relations.',
                'thumbnail' => 'images/themes/business/corporate-preview.png',
                'public' => true,
                'free' => false,
                'price' => 4999,
            ],
        ],
    ],
];
