<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.product_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>:root { --primary: {{ config('product.primary_color') }}; }</style>
</head>
<body class="guest">
    <main class="guest-wrap">
        <a class="brand guest-brand" href="{{ route('home') }}">
            @if(config('product.logo_path'))<img src="{{ route('branding.logo', 'global') }}" alt="{{ config('app.product_name') }}">@else<span class="brand-mark" aria-hidden="true">▥</span>@endif
            <span>{{ config('app.product_name') }}</span>
        </a>
        @include('partials.messages')
        @yield('content')
    </main>
</body>
</html>

