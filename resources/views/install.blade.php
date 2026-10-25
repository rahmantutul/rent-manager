@extends('layouts.guest')
@section('title', __('app.install'))
@section('content')
<div class="page-heading"><div><div class="eyebrow">{{ __('app.setup') }}</div><h1>{{ __('app.install') }}</h1><p class="muted">{{ __('app.install_intro') }}</p></div></div>
<section class="card"><h2>{{ __('app.requirements') }}</h2><p class="muted">{{ __('app.php_version', ['version' => PHP_VERSION]) }}</p><div class="requirements">
@foreach($checks as $name => $passed)<div><span>{{ $name }}</span><span class="badge {{ $passed ? '' : 'failed' }}">{{ __($passed ? 'app.passed' : 'app.failed') }}</span></div>@endforeach
</div></section>
<form method="post" action="{{ route('install.store') }}" class="stack install-form" x-data="{ submitting: false }" @submit="submitting = true">@csrf
<section class="card"><h2>{{ __('app.database') }}</h2><p class="muted">{{ __('app.database_help') }}</p><div class="form-grid">
<x-field name="app_url" :value="url('/')" required type="url" />
<x-field name="db_host" value="127.0.0.1" required />
<x-field name="db_port" type="number" value="3306" required />
<x-field name="db_database" required />
<x-field name="db_username" required autocomplete="off" />
<x-field name="db_password" type="password" autocomplete="new-password" />
</div></section>
<section class="card"><h2>{{ __('app.first_account') }}</h2><div class="form-grid">
<x-field name="company_name" required /><x-field name="name" required />
<x-field name="email" type="email" required /><x-field name="phone" />
<x-field name="password" type="password" required minlength="12" autocomplete="new-password" />
<x-field name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" />
<x-field name="currency_code" value="USD" required maxlength="3" />
<label class="field"><span>{{ __('app.minor_units') }}</span><select name="minor_units">@foreach([0, 2, 3] as $units)<option value="{{ $units }}" @selected((int) old('minor_units', 2) === $units)>{{ $units }}</option>@endforeach</select></label>
<x-field name="timezone" value="UTC" required />
<label class="field"><span>{{ __('app.locale') }}</span><select name="locale">@foreach(config('product.locales') as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach</select></label>
</div></section>
<section class="card"><h2>{{ __('app.mail_setup') }}</h2><div class="form-grid">
<label class="field"><span>{{ __('app.mail_mailer') }}</span><select name="mail_mailer"><option value="log" @selected(old('mail_mailer') === 'log')>{{ __('app.mail_log') }}</option><option value="smtp" @selected(old('mail_mailer') === 'smtp')>{{ __('app.mail_smtp') }}</option></select></label>
<x-field name="mail_host" /><x-field name="mail_port" type="number" value="587" />
<x-field name="mail_username" autocomplete="off" /><x-field name="mail_password" type="password" autocomplete="new-password" />
<label class="field"><span>{{ __('app.mail_scheme') }}</span><select name="mail_scheme"><option value="smtp">{{ __('app.smtp_starttls') }}</option><option value="smtps">{{ __('app.smtps') }}</option></select></label>
</div></section>
<p class="muted">{{ __('app.key_warning') }}</p>
<button @disabled(in_array(false, $checks, true)) :disabled="submitting">{{ __('app.complete_install') }}</button>
</form>
@endsection

