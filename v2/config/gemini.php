<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Gemini API Configuration
    |--------------------------------------------------------------------------
    */
    'api_key'   => env('GEMINI_API_KEY'),
    'base_url'  => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    */
    'chat_model'   => env('GEMINI_CHAT_MODEL', 'gemini-2.5-flash-latest'),
    'vision_model' => env('GEMINI_VISION_MODEL', 'gemini-2.5-flash-latest'), // Vision model nên dùng Pro để có chất lượng tốt hơn
    'video_model'  => env('GEMINI_VIDEO_MODEL', 'veo-2'), // Model cho video là 'veo-2'

    /*
    |--------------------------------------------------------------------------
    | Google Veo (Text-to-Video)
    |--------------------------------------------------------------------------
    */
    'video_api_key' => env('GOOGLE_VIDEO_API_KEY'),
    'veo_api_url'   => env('VEO_API_URL', 'https://generativelanguage.googleapis.com/v1beta'),

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    */
    'upload_url'       => env('GEMINI_UPLOAD_URL', 'https://generativelanguage.googleapis.com/upload/v1beta'),
    'max_file_size_mb' => env('MAX_FILE_SIZE', 20),
    'allowed_types'    => explode(',', env('ALLOWED_FILE_TYPES', 'pdf,txt,docx,jpg,jpeg,png,gif,mp4,mov,mp3,wav')),

    /*
    |--------------------------------------------------------------------------
    | Generation Defaults
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | SSL / cURL Configuration
    |--------------------------------------------------------------------------
    | Trên Windows/XAMPP/Laragon thường gặp lỗi cURL error 60 do thiếu CA cert.
    |
    | Cách 1 (Khuyên dùng): Tải cacert.pem và đặt vào storage/app/cacert.pem
    |   https://curl.se/ca/cacert.pem
    |
    | Cách 2: Set đường dẫn tuyệt đối
    |   GEMINI_SSL_CA_CERT=C:/xampp/php/extras/ssl/cacert.pem
    |
    | Cách 3: Tắt verify (CHỈ dùng local dev, KHÔNG dùng production)
    |   GEMINI_SSL_VERIFY=false
    */
    'ssl_ca_cert' => env('GEMINI_SSL_CA_CERT'),
    'ssl_verify'  => env('GEMINI_SSL_VERIFY', true) === 'false' ? false : (bool) env('GEMINI_SSL_VERIFY', true),

    'defaults' => [
        'temperature'      => 0.9,
        'top_k'            => 40,
        'top_p'            => 0.95,
        'max_output_tokens' => 8192,
    ],
];
