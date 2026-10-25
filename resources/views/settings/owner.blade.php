@extends('layouts.admin')
@section('title', __('app.settings'))
@section('content')
<div class="page-heading"><div><div class="eyebrow">{{ __('app.workspace') }}</div><h1>{{ __('app.company_settings') }}</h1><p class="muted">{{ __('app.settings_intro') }}</p></div></div>
<form action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data" class="stack">@csrf @method('PUT')
<section class="card"><h2>{{ __('app.company_details') }}</h2><div class="form-grid">
<x-field name="name" :value="$owner->name" required /><x-field name="email" type="email" :value="$owner->email" required />
<x-field name="phone" :value="$owner->phone" /><x-field name="company_address" :value="$address" />
<x-field name="logo" type="file" accept="image/png,image/jpeg,image/webp" />
</div><p class="muted">{{ __('app.logo_help') }}</p>
@if($owner->logo_path)<img class="logo-preview" src="{{ route('branding.logo', 'owner') }}" alt="{{ $owner->name }}">@endif
</section>
<section class="card"><h2>{{ __('app.regional_settings') }}</h2><div class="form-grid">
<x-field name="currency_code" :value="$owner->currency_code" required maxlength="3" />
<label class="field"><span>{{ __('app.minor_units') }}</span><select name="minor_units">@foreach([0, 2, 3] as $units)<option value="{{ $units }}" @selected((int) old('minor_units', $owner->minor_units) === $units)>{{ $units }}</option>@endforeach</select></label>
<x-field name="timezone" :value="$owner->timezone" required list="timezones" />
<datalist id="timezones">@foreach(timezone_identifiers_list() as $zone)<option value="{{ $zone }}">@endforeach</datalist>
<label class="field"><span>{{ __('app.locale') }}</span><select name="locale">@foreach(config('product.locales') as $code => $label)<option value="{{ $code }}" @selected(old('locale', $owner->locale) === $code)>{{ $label }}</option>@endforeach</select></label>
<label class="field"><span>{{ __('app.date_format') }}</span><select name="date_format">@foreach(config('product.date_formats') as $format)<option value="{{ $format }}" @selected(old('date_format', $owner->date_format) === $format)>{{ now()->format($format) }}</option>@endforeach</select></label>
</div></section>
<div class="form-actions"><button>{{ __('app.save') }}</button></div>
</form>
@endsection

