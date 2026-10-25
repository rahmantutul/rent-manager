<?php

namespace App\Http\Middleware;

use App\Services\Installation;
use Closure;

class EnsureInstalled
{
    public function handle($request, Closure $next)
    {
        if (! app(Installation::class)->installed()) {
            return redirect()->route('install.show');
        }

        return $next($request);
    }
}
