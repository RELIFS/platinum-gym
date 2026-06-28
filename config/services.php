<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'snap_sandbox_url' => env('MIDTRANS_SNAP_SANDBOX_URL', 'https://app.sandbox.midtrans.com/snap/v1'),
        'snap_production_url' => env('MIDTRANS_SNAP_PRODUCTION_URL', 'https://app.midtrans.com/snap/v1'),
        'api_sandbox_url' => env('MIDTRANS_API_SANDBOX_URL', 'https://api.sandbox.midtrans.com'),
        'api_production_url' => env('MIDTRANS_API_PRODUCTION_URL', 'https://api.midtrans.com'),
        'timeout' => env('MIDTRANS_TIMEOUT', 10),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'api_keys' => array_values(array_filter(array_map(
            fn (string $key): string => trim($key, " \t\n\r\0\x0B\"'"),
            preg_split('/[\r\n,]+/', (string) env('GEMINI_API_KEYS', '')) ?: [],
        ))),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'base_url' => env('GEMINI_API_BASE_URL', 'https://generativelanguage.googleapis.com'),
        'enabled' => env('GYMMI_AI_ENABLED', true),
        'timeout' => env('GEMINI_TIMEOUT', env('GYMMI_AI_TIMEOUT', 12)),
        'connect_timeout' => env('GYMMI_AI_CONNECT_TIMEOUT', 5),
        'max_retries' => env('GEMINI_MAX_RETRIES', 2),
        'max_output_tokens' => env('GYMMI_AI_MAX_OUTPUT_TOKENS', 500),
        'temperature' => env('GYMMI_AI_TEMPERATURE', 0.45),
        'rate_limit_per_minute' => env('GYMMI_AI_RATE_LIMIT_PER_MINUTE', 12),
        'circuit_breaker_seconds' => env('GYMMI_AI_CIRCUIT_BREAKER_SECONDS', 300),
        'key_cooldown_seconds' => env('GYMMI_AI_KEY_COOLDOWN_SECONDS', 120),
        'public_cache_seconds' => env('GYMMI_AI_PUBLIC_CACHE_SECONDS', 900),
    ],

    'pddikti' => [
        'enabled' => env('PDDIKTI_ENABLED', false),
        'base_url' => env('PDDIKTI_BASE_URL'),
        'token' => env('PDDIKTI_TOKEN'),
        'timeout' => env('PDDIKTI_TIMEOUT', 10),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
