@extends('layouts.admin')
@section('title', __('app.branding'))
@section('content')
<div class="page-heading"><div><div class="eyebrow">{{ __('app.super_admin') }}</div><h1>{{ __('app.branding') }}</h1><p class="muted">{{ __('app.branding_intro') }}</p></div></div>
<form action="{{ route('super-admin.branding.update') }}" method="post" enctype="multipart/form-data" class="stack">@csrf @method('PUT')
<section class="card"><h2>{{ __('app.brand_identity') }}</h2><div class="form-grid">
<x-field name="product_name" :value="config('app.product_name')" required />
<x-field name="primary_color" type="color" :value="config('product.primary_color')" required />
<x-field name="logo" type="file" accept="image/png,image/jpeg,image/webp" />
</div><p class="muted">{{ __('app.logo_help') }}</p></section>
<div class="form-actions"><button>{{ __('app.save') }}</button></div>
</form>
@endsection

