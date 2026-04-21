<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowCreateTicket
{
    /**
     * Allow access to ticket creation for both CABANG and IT roles.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $role = $user->role ?? null;
        if (in_array($role, ['IT', 'CABANG'], true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
