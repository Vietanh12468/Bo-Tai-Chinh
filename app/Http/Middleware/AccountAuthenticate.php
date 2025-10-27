<?php

namespace App\Http\Middleware;

use App\ApiResponser;
use App\Models\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountAuthenticate
{
    use ApiResponser;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $method = $request->getRealMethod();
        $url = $request->url();

        if (!empty($token)) {
            $checkToken = PersonalAccessToken::where('token', hash('sha256', $token))->with('user')->first();

            if (!empty($checkToken)) {
                $account = $checkToken->toArray();

                $accountInfo = $account['user'];

                $request->merge(compact('accountInfo'));

                if ($request->has('name')) {
                    $slug = Str::slug($request->get('name'));
                    $request->merge(compact('slug'));
                }

                return $next($request);
            }
        }

        return $this->sendError(__('auth.failed'), [], 500);
    }
}
