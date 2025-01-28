<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'login', 'register','storage/*','*'], // مسیرهای مربوطه را اضافه کنید. // مسیرهای مجاز برای CORS

    'allowed_methods' => ['*'], // متدهای مجاز (مانند GET, POST)

    'allowed_origins' => ['*'], // منابع مجاز (مانند دامنه‌ها)

    'allowed_origins_patterns' => ['*'], // الگوهای مجاز برای منابع

    'allowed_headers' => ['*'], // هدرهای مجاز

    'exposed_headers' => ['*'], // هدرهایی که می‌توانند نمایش داده شوند

    'max_age' => 0, // مدت زمان معتبر بودن درخواست

    'supports_credentials' => false, // آیا اعتبارسنجی‌ها (مانند کوکی‌ها) پشتیبانی می‌شوند؟

];
