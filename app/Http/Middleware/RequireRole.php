<?php

namespace App\Http\Middleware;

use Closure;

class RequireRole
{
    public function handle($request, Closure $next, string ...$roles)
    {
        abort_unless($request->user()?->canSignIn() && in_array($request->user()->role->value, $roles, true), 403);

        return $next($request);
    }
}
