<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.product_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>:root { --primary: {{ config('product.primary_color') }}; }</style>
</head>
<body x-data="{ menuOpen: false }">
    <aside class="sidebar" :class="{ 'is-open': menuOpen }">
        <a class="brand" href="{{ route(auth()->user()->role->landingRoute()) }}">
            @if(auth()->user()->owner?->logo_path)<img src="{{ route('branding.logo', 'owner') }}" alt="{{ auth()->user()->owner->name }}">
            @elseif(config('product.logo_path'))<img src="{{ route('branding.logo', 'global') }}" alt="{{ config('app.product_name') }}">
            @else<span class="brand-mark" aria-hidden="true">▥</span>@endif
            <span>{{ config('app.product_name') }}</span>
        </a>
        <div class="nav-caption">{{ __('app.workspace') }}</div>
        <nav>
            <a class="{{ request()->routeIs('*.dashboard') ? 'active' : '' }}" href="{{ route(auth()->user()->role->landingRoute()) }}">{{ __('app.dashboard') }}</a>
            @if(auth()->user()->role->value !== 'tenant' && auth()->user()->role->value !== 'super_admin')<a class="{{ request()->routeIs('admin.properties.*', 'admin.buildings.*', 'admin.units.*') ? 'active' : '' }}" href="{{ route('admin.properties.index') }}">{{ __('app.properties') }}</a>@endif
            @can('manage-owner-settings')<a class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">{{ __('app.settings') }}</a>@endcan
            @can('manage-global-branding')<a class="{{ request()->routeIs('super-admin.branding.*') ? 'active' : '' }}" href="{{ route('super-admin.branding.edit') }}">{{ __('app.branding') }}</a>@endcan
        </nav>
        <div class="sidebar-bottom">{{ auth()->user()->owner?->name ?? __('app.super_admin') }}</div>
    </aside>
    <div class="shell">
        <header class="topbar">
            <button class="menu-button secondary" type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen">{{ __('app.menu') }}</button>
            <span class="breadcrumb">{{ __('app.workspace') }} <span>/</span> @yield('title')</span>
            <div class="account">
                <form method="post" action="{{ route('locale.update') }}">@csrf
                    <label class="sr-only" for="locale-switch">{{ __('app.locale') }}</label>
                    <select name="locale" id="locale-switch" onchange="this.form.requestSubmit()">
                        @foreach(config('product.locales') as $code => $label)<option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $label }}</option>@endforeach
                    </select>
                </form>
                <span>{{ auth()->user()->name }}<small>{{ __('app.'.auth()->user()->role->value) }}</small></span>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="secondary">{{ __('app.logout') }}</button></form>
            </div>
        </header>
        <main class="content">@include('partials.messages')@yield('content')</main>
    </div>
</body>
</html>
