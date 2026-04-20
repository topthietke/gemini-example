<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Gemini API Key
    |--------------------------------------------------------------------------
    | Get yours at: https://aistudio.google.com/app/apikey
    */
    'api_key' => env('GEMINI_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    | Available: gemini-2.0-flash, gemini-1.5-pro, gemini-1.5-flash
    */
    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),

    /*
    |--------------------------------------------------------------------------
    | Generation Config
    |--------------------------------------------------------------------------
    */
    'temperature' => env('GEMINI_TEMPERATURE', 0.7),
    'max_tokens'  => env('GEMINI_MAX_TOKENS', 2048),
];
