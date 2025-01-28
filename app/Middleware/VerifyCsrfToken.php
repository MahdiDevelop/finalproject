<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'login', // مسیرهایی که می‌خواهید CSRF برای آن‌ها غیرفعال شود
        'api/*',
        // می‌توانید مسیرهای بیشتری اضافه کنید
    ];
}
