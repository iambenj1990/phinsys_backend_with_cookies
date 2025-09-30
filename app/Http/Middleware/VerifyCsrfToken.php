<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Add your routes here if you want them excluded from CSRF
        'api/*',        // Exclude all API routes
        'sanctum/csrf-cookie', // Exclude Sanctum's CSRF endpoint
    ];
}
