@extends('layouts.guest')
@section('title', __('app.install'))
@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ submitting: false }">
    <div class="text-center mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">{{ __('app.setup') }}</p>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ __('app.install') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('app.install_intro') }}</p>
    </div>

    <ol class="grid grid-cols-3 gap-2 text-xs font-semibold text-center" aria-label="Installation progress">
        <li class="rounded-full bg-blue-600 px-3 py-2 text-white">1 · {{ __('app.server_step') }}</li>
        <li class="rounded-full bg-blue-50 px-3 py-2 text-blue-700">2 · {{ __('app.database_step') }}</li>
        <li class="rounded-full bg-slate-100 px-3 py-2 text-slate-500">3 · {{ __('app.account_step') }}</li>
    </ol>

    <section class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <div class="flex items-center justify-between mb-5"><div><h2 class="text-lg font-semibold text-slate-900">{{ __('app.requirements') }}</h2><p class="text-sm text-slate-500">{{ __('app.php_version', ['version' => PHP_VERSION]) }}</p></div><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ count(array_filter($checks)) }}/{{ count($checks) }} ready</span></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($checks as $name => $passed)
                <div class="flex items-center gap-3 rounded-lg border {{ $passed ? 'border-emerald-100 bg-emerald-50' : 'border-red-100 bg-red-50' }} px-3 py-3 text-sm">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full {{ $passed ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white' }}" aria-hidden="true">{{ $passed ? '✓' : '×' }}</span><span class="truncate text-slate-700">{{ $name }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <form method="post" action="{{ route('install.store') }}" class="space-y-6" @submit="submitting = true">@csrf
        <section class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8"><div class="mb-6"><h2 class="text-lg font-semibold text-slate-900">{{ __('app.database') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('app.database_help') }}</p></div><div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-field name="app_url" :value="url('/')" required type="url" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
            <x-field name="db_host" value="127.0.0.1" required class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
            <x-field name="db_port" type="number" value="3306" required class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
            <x-field name="db_database" required class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 {{ $errors->has('db_database') ? 'border-red-500 ring-2 ring-red-100' : '' }}" />
            <x-field name="db_username" required autocomplete="off" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 {{ $errors->has('db_username') ? 'border-red-500 ring-2 ring-red-100' : '' }}" />
            <x-field name="db_password" type="password" autocomplete="new-password" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 {{ $errors->has('db_password') ? 'border-red-500 ring-2 ring-red-100' : '' }}" />
        </div></section>

        <section class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8"><div class="mb-6"><h2 class="text-lg font-semibold text-slate-900">{{ __('app.first_account') }}</h2></div><div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-field name="company_name" required dir="auto" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" /><x-field name="name" required dir="auto" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
            <x-field name="email" type="email" required class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" /><x-field name="phone" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
            <x-field name="password" type="password" required minlength="12" autocomplete="new-password" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" /><x-field name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
            <x-field name="currency_code" value="USD" required maxlength="3" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" /><x-field name="timezone" value="UTC" required class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
            <label class="field"><span>{{ __('app.minor_units') }}</span><select name="minor_units" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">@foreach([0,2,3] as $units)<option value="{{ $units }}" @selected((int) old('minor_units',2)===$units)>{{ $units }}</option>@endforeach</select></label>
            <label class="field"><span>{{ __('app.locale') }}</span><select name="locale" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">@foreach(config('product.locales') as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach</select></label>
        </div></section>

        <section class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8"><div class="mb-6"><h2 class="text-lg font-semibold text-slate-900">{{ __('app.mail_setup') }}</h2></div><div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <label class="field md:col-span-2"><span>{{ __('app.mail_mailer') }}</span><select name="mail_mailer" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200"><option value="log" @selected(old('mail_mailer')==='log')>{{ __('app.mail_log') }}</option><option value="smtp" @selected(old('mail_mailer')==='smtp')>{{ __('app.mail_smtp') }}</option></select><small class="text-xs font-normal text-slate-500">{{ __('app.mail_helper') }}</small></label>
            <x-field name="mail_host" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" /><x-field name="mail_port" type="number" value="587" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" /><x-field name="mail_username" autocomplete="off" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" /><x-field name="mail_password" type="password" autocomplete="new-password" class="rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
        </div></section>
        <div class="max-w-3xl mx-auto rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 text-sm text-blue-800">{{ __('app.key_warning') }}</div>
        <button type="submit" class="w-full max-w-3xl mx-auto flex rounded-xl bg-blue-600 px-6 py-4 text-base font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 disabled:cursor-wait disabled:opacity-60" @disabled(in_array(false,$checks,true)) :disabled="submitting"><span x-show="!submitting">{{ __('app.complete_install') }}</span><span x-show="submitting" x-cloak>{{ __('app.installing') }}</span></button>
    </form>
</div>
@endsection
