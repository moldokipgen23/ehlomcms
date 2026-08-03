<?php

return [
    'school' => [
        'label' => 'School',
        'template' => 'school/classic',
        // School is a website-management workspace, not a school ERP.
        // These modules power the public information site and its enquiries.
        'default_modules' => [
            'content', 'hero', 'stats', 'about', 'academics', 'admissions',
            'faculty', 'student_life', 'facilities', 'gallery', 'news',
            'achievements', 'testimonials', 'downloads', 'certificates',
            'contact', 'why_choose', 'map', 'enquiry_form',
        ],
    ],
    'shopping' => [
        'label' => 'Shopping / Store',
        'template' => 'shopping/classic',
        'default_modules' => ['content', 'catalog', 'product_categories', 'product_gallery', 'cart', 'checkout', 'payments', 'orders', 'product_collections', 'inventory', 'search_filters', 'marketing_sections', 'wishlist'],
    ],
    'restaurant' => [
        'label' => 'Restaurant',
        'template' => 'restaurant/classic',
        'default_modules' => ['content', 'catalog', 'reservations', 'orders'],
    ],
    'business' => [
        'label' => 'Portfolio / Business',
        'template' => 'business/classic',
        'default_modules' => ['content', 'services', 'testimonials', 'blog', 'case_studies', 'team', 'careers', 'newsletter', 'enquiries'],
    ],
];
