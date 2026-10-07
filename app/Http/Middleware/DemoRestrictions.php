<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoRestrictions
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('demo.enabled') && $request->routeIs('profile.update', 'profile.destroy', 'password.update')) {
            abort(403, 'Fitur ini dimatikan di versi coba.');
        }

        return $next($request);
    }
}