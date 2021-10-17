<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Utils\JsonResponse;
use Closure;
use Illuminate\Http\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (!User::hasRole($role)->first()) {
            return response()->json(new JsonResponse(
                'Akses hanya untuk '. ucfirst($role),
                ['data' => ''],
                'unauthorized_error'
            ), Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
