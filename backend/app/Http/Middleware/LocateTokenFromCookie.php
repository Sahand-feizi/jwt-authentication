<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocateTokenFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->headers->has('Authorization') && $request->hasCookie('access_token')) {
            $token = $request->cookie('access_token');
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
