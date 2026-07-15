<?php

return [
    'school' => [
        'label' => 'School',
        'template' => 'school/classic',
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
