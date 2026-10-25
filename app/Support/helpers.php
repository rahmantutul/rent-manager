<?php

use App\Support\Money;

if (! function_exists('floor_label')) {
    function floor_label(int $floor): string
    {
        return $floor === 0 ? __('app.ground_floor') : ($floor < 0 ? __('app.basement_floor', ['number' => abs($floor)]) : __('app.floor_number', ['number' => $floor]));
    }
}

if (! function_exists('money')) {
    function money(int $minor, ?string $currency = null, ?int $units = null): string
    {
        $owner = auth()->user()?->owner;
        $currency ??= $owner?->currency_code ?? 'USD';
        $units ??= $owner?->minor_units ?? 2;

        return Money::fromMinor($minor, $currency, $units)->format($owner?->locale ?? 'en_US');
    }
}
if (! function_exists('money_input_to_minor')) {
    function money_input_to_minor(string $value, ?string $currency = null, ?int $units = null): int
    {
        $owner = auth()->user()?->owner;

        return Money::parse($value, $currency ?? $owner?->currency_code ?? 'USD', $units ?? $owner?->minor_units ?? 2)->minor;
    }
}
