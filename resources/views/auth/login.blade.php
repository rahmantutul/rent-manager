@extends('layouts.guest')
@section('title', __('app.login'))
@section('content')
<section class="card login-card">
    <div class="eyebrow">{{ __('app.welcome') }}</div><h1>{{ __('app.login') }}</h1><p class="muted">{{ __('app.login_intro') }}</p>
    <form method="post" action="{{ route('login.store') }}" class="stack">@csrf
        <x-field name="email" type="email" required autocomplete="username" />
        <x-field name="password" type="password" required autocomplete="current-password" />
        <button>{{ __('app.login') }} <span aria-hidden="true">→</span></button>
    </form>
</section>
@endsection

