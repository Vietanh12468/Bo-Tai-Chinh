<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class LoginThrottle
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $executed = RateLimiter::attempt(
            key: 'login:' . $ip,
            maxAttempts: 3,
            decaySeconds: 60,
            callback: function () {
                return true;
            }
        );

        if (! $executed) {
            return response()->json([
                'error_code' => 1,
                'notification' => __('auth.too_many_login_attempts'),
                'data' => []
            ]);
        }

        return $next($request);
    }
}
