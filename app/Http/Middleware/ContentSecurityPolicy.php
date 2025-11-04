<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $csp = implode('; ', [
            "default-src 'none'",
            "script-src 'self'",
            "style-src 'self'",
            "img-src 'self'",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'none'",
            "base-uri 'none'",
        ]) . ';';

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
