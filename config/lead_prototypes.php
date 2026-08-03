<?php

return [
    'school' => [
        'label' => 'School Website Demo',
        'theme_key' => 'school/eiho',
        'demo_url' => env('SCHOOL_DEMO_URL', 'https://eihoschooldemo.ehlom.com/'),
        'offer_label' => 'School Website Module',
        'keywords' => ['school', 'academy', 'college', 'kindergarten', 'nursery', 'public school'],
    ],
    'restaurant' => [
        'label' => 'Restaurant Website Demo',
        'theme_key' => 'restaurant/classic',
        // Keep this empty until a real restaurant demo tenant is published.
        'demo_url' => env('RESTAURANT_DEMO_URL'),
        'offer_label' => 'Restaurant Website Module',
        'keywords' => ['restaurant', 'cafe', 'coffee', 'bakery', 'bistro', 'food', 'dining'],
    ],
    'shopping' => [
        'label' => 'Fashion Store Demo',
        'theme_key' => 'brandshop',
        'demo_url' => env('SHOPPING_DEMO_URL', 'https://brandshopdemo.ehlom.com/'),
        'offer_label' => 'Shopping Store Module',
        'keywords' => ['shop', 'store', 'boutique', 'clothing', 'fashion', 'retail', 'jewellery', 'accessories'],
    ],
    'business' => [
        'label' => 'Business Website Demo',
        'theme_key' => 'business',
        'demo_url' => env('BUSINESS_DEMO_URL', 'https://portfoliodemo.ehlom.com/'),
        'offer_label' => 'Portfolio / Business Website Module',
        'keywords' => ['agency', 'consultant', 'studio', 'service', 'professional', 'lawyer', 'designer'],
    ],
];
