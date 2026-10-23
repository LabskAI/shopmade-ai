<?php

return [

    /*
    | The OpenAI-compatible chat endpoint packages call through Shopmade\Ai\ChatProvider.
    | `chat.provider` picks the vendor; its key and base URL come from `providers.<name>`,
    | the model from `models.chat` or the vendor's default.
    */

    'chat' => [
        'provider' => env('AI_CHAT_PROVIDER', env('AI_PROVIDER', 'xai')),
    ],

    'providers' => [
        'xai' => [
            'key' => env('XAI_API_KEY', env('AI_API_KEY')),
            'base_url' => env('AI_XAI_BASE_URL', 'https://api.x.ai/v1'),
        ],
        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY', env('AI_API_KEY')),
            'base_url' => env('AI_ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
        ],
        'openrouter' => [
            'key' => env('OPENROUTER_API_KEY'),
            'base_url' => env('AI_OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        ],
    ],

    'models' => [
        'chat' => env('AI_CHAT_MODEL', env('AI_PM_MODEL')),
    ],

];
