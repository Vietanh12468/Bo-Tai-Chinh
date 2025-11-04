<?php

namespace App\Http\Middleware;

use App\ApiResponser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class PermissionsAuthenticate
{
    use ApiResponser;
    /**
     * Handle an incoming request from AccountAuthentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()->getName();

        $accountInfo = $request->get('accountInfo');

        if (!$accountInfo) {
            return $this->sendError(__('auth.failed'), [], 500);
        }
        $user = User::find($accountInfo['id']);

        if (!$user->checkPermission($routeName)) {
            return $this->sendError(__('auth.permission_denied'));
        }

        return $next($request);
    }
}
