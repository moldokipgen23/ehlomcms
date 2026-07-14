<?php

/**
 * The single source of truth for the platform's business types (verticals).
 *
 * Each business type maps to: a human label, the default storefront template
 * key (a Theme->key), and the set of module keys (see config/modules.php) that
 * are enabled by default when a tenant of this type is created. Adding a new
 * vertical here + its module rows in config/modules.php + a storefront template
 * makes it a first-class option across the admin (create form, Business Modules
 * page) without hunting through hardcoded enums.
 *
 * Note: the site_type validation lists in AdminTenantController still enumerate
 * these keys explicitly; keep them in sync until that validation reads from
 * array_keys(config('business_types')).
 */

return [
    'info' => [
        'label' => 'Info / Portfolio',
        'template' => 'info',
        'default_modules' => ['content'],
    ],
    'shopping' => [
        'label' => 'Shopping / Store',
        'template' => 'shop',
        'default_modules' => ['content', 'catalog', 'orders'],
    ],
    'restaurant' => [
        'label' => 'Restaurant',
        'template' => 'restaurant',
        'default_modules' => ['content', 'catalog', 'reservations', 'orders'],
    ],
    'business' => [
        'label' => 'Portfolio / Business',
        'template' => 'business',
        'default_modules' => ['content', 'services', 'testimonials', 'blog'],
    ],
];
