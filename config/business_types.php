<?php

/**
 * The single source of truth for the platform's business types (verticals).
 *
 * Each business type maps to: a human label, the default storefront template
 * key (a Theme->key), and the set of module keys (see config/modules.php) that
 * are enabled by default when a tenant of this type is created.
 */

return [
    'info' => [
        'label' => 'Info / Basic',
        'template' => 'info/classic',
        'default_modules' => ['content'],
    ],
    'shopping' => [
        'label' => 'Shopping / Store',
        'template' => 'shopping/classic',
        'default_modules' => ['content', 'catalog', 'orders'],
    ],
    'restaurant' => [
        'label' => 'Restaurant',
        'template' => 'restaurant/classic',
        'default_modules' => ['content', 'catalog', 'reservations', 'orders'],
    ],
    'business' => [
        'label' => 'Portfolio / Business',
        'template' => 'business/classic',
        'default_modules' => ['content', 'services', 'testimonials', 'blog'],
    ],
];