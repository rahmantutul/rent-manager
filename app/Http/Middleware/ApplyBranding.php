<?php

namespace App\Http\Middleware;

use App\Services\Installation;
use Closure;
use Illuminate\Support\Facades\DB;

class ApplyBranding
{
    public function handle($request, Closure $next)
    {
        if (app(Installation::class)->installed()) {
            // Deliberate public allowlist: never expose other global/owner settings.
            $branding = DB::table('settings')->whereNull('owner_id')->whereIn('key', ['product_name', 'primary_color', 'logo_path'])->pluck('value', 'key');
            config(['app.product_name' => $branding['product_name'] ?? config('app.product_name'), 'product.primary_color' => $branding['primary_color'] ?? config('product.primary_color'), 'product.logo_path' => $branding['logo_path'] ?? null]);
            $locale = $request->user()?->locale ?? $request->user()?->owner?->locale ?? config('app.locale');
            app()->setLocale(array_key_exists($locale, config('product.locales')) ? $locale : 'en');
        }

        return $next($request);
    }
}
