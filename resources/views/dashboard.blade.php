@extends('layouts.admin')
@section('title', __('app.dashboard'))
@section('content')
<div class="page-heading"><div><div class="eyebrow">{{ __('app.'.$area) }}</div><h1>{{ __('app.welcome_name', ['name' => auth()->user()->name]) }}</h1><p class="muted">{{ __('app.dashboard_intro') }}</p></div><span class="badge">{{ __('app.ready') }}</span></div>
<section class="card empty-state">
    <div class="empty-icon" aria-hidden="true">▥</div>
    <h2>{{ __('app.'.$area.'_placeholder') }}</h2>
    <p class="muted">{{ __('app.foundation_only') }}</p>
    @can('manage-owner-settings')<a class="button" href="{{ route('admin.settings.edit') }}">{{ __('app.company_settings') }}</a>@endcan
    @can('manage-global-branding')<a class="button" href="{{ route('super-admin.branding.edit') }}">{{ __('app.branding') }}</a>@endcan
</section>
@endsection

