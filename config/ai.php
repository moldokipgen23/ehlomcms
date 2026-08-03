<?php

return [
    'providers' => [
        'openai' => [
            'label' => 'OpenAI',
            'default_model' => 'gpt-5-mini',
            'base_url' => 'https://api.openai.com/v1',
        ],
        'gemini' => [
            'label' => 'Google Gemini',
            'default_model' => 'gemini-2.5-flash',
            'base_url' => 'https://generativelanguage.googleapis.com',
        ],
        'deepseek' => [
            'label' => 'DeepSeek',
            'default_model' => 'deepseek-v4-flash',
            'base_url' => 'https://api.deepseek.com',
        ],
        'anthropic' => [
            'label' => 'Anthropic',
            'default_model' => 'claude-sonnet-4-0',
            'base_url' => 'https://api.anthropic.com',
        ],
        'custom' => [
            'label' => 'Custom OpenAI-compatible',
            'default_model' => '',
            'base_url' => '',
        ],
    ],
];
