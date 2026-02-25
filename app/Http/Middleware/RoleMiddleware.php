<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
public function handle($request, Closure $next, $role)
    {
        // allow multiple roles separated by | and compare case-insensitive
        if (! auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $userRole = strtolower((string) auth()->user()->role);
        $allowed = array_map('strtolower', explode('|', $role));

        if (in_array($userRole, $allowed, true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
    
}
