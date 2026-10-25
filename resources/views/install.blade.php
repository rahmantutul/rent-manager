@extends('layouts.guest')

@section('title', __('app.install'))

@section('content')
<div class="min-h-screen flex bg-slate-50">
    
    <!-- Left Side: Branding (Hidden on mobile) -->
    <div class="hidden lg:flex lg:w-[40%] flex-col justify-between bg-slate-900 p-12 text-white relative overflow-hidden">
        <!-- Background Decoration -->
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/50 to-slate-900/50"></div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
        
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-12">
                <div class="h-10 w-10 bg-indigo-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-xl font-bold tracking-tight">{{ config('app.product_name') }}</span>
            </div>

            <h1 class="text-4xl font-bold leading-tight mb-6">Set up your premium workspace.</h1>
            <p class="text-slate-300 text-lg mb-8">Join thousands of landlords and property managers who automate their rent collection and building management.</p>
            
            <ul class="space-y-4">
                <li class="flex items-center gap-3 text-slate-200">
                    <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    Multi-role access (Owner, Manager, Tenant)
                </li>
                <li class="flex items-center gap-3 text-slate-200">
                    <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    Automated recurring invoices
                </li>
                <li class="flex items-center gap-3 text-slate-200">
                    <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    Beautiful PDF tenant statements
                </li>
            </ul>
        </div>
        
        <div class="relative z-10 text-sm text-slate-500">
            &copy; {{ date('Y') }} {{ config('app.product_name') }}. All rights reserved.
        </div>
    </div>

    <!-- Right Side: The Form -->
    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24 overflow-y-auto max-h-screen">
        <div class="mx-auto w-full max-w-2xl lg:w-[36rem]">
            
            <!-- Mobile Logo -->
            <div class="lg:hidden flex items-center gap-3 mb-8">
                <div class="h-10 w-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-xl font-bold text-slate-900">{{ config('app.product_name') }}</span>
            </div>

            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600 mb-2">{{ __('app.setup') }}</p>
                <h2 class="text-3xl font-extrabold text-slate-900">{{ __('app.install') }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ __('app.install_intro') }}</p>
            </div>

            <!-- Progress Bar -->
            <ol class="flex items-center w-full mb-8 text-sm font-medium text-center text-slate-500">
                <li class="flex items-center text-indigo-600 w-full after:content-[''] after:w-full after:h-1 after:border-b after:border-indigo-200 after:border-2 after:inline-block">
                    <span class="flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full shrink-0 mr-2">1</span>
                    <span class="hidden sm:inline">{{ __('app.server_step') }}</span>
                </li>
                <li class="flex items-center text-indigo-600 w-full after:content-[''] after:w-full after:h-1 after:border-b after:border-indigo-200 after:border-2 after:inline-block">
                    <span class="flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full shrink-0 mx-2">2</span>
                    <span class="hidden sm:inline">{{ __('app.database_step') }}</span>
                </li>
                <li class="flex items-center text-slate-400">
                    <span class="flex items-center justify-center w-8 h-8 bg-slate-100 text-slate-400 rounded-full shrink-0 mx-2">3</span>
                    <span class="hidden sm:inline">{{ __('app.account_step') }}</span>
                </li>
            </ol>

            <!-- Exact Error Display -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Installation Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('install.store') }}" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <!-- Server Requirements -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                        <h3 class="text-base font-semibold text-slate-900">{{ __('app.requirements') }}</h3>
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                            {{ count(array_filter($checks)) }}/{{ count($checks) }} Ready
                        </span>
                    </div>
                    <div class="p-5 grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($checks as $name => $passed)
                            <div class="flex items-center gap-2">
                                @if($passed)
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                @else
                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                @endif
                                <span class="text-sm {{ $passed ? 'text-slate-700' : 'text-red-700 font-medium' }}">{{ $name === 'php' ? 'PHP '.PHP_VERSION : $name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Database Setup -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-base font-semibold text-slate-900">{{ __('app.database') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ __('app.database_help') }}</p>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">App URL</label>
                            <input type="url" name="app_url" value="{{ old('app_url', url('/')) }}" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('app_url') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Database Host</label>
                            <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('db_host') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Database Port</label>
                            <input type="number" name="db_port" value="{{ old('db_port', '3306') }}" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('db_port') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Database Name</label>
                            <input type="text" name="db_database" value="{{ old('db_database') }}" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('db_database') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Database Username</label>
                            <input type="text" name="db_username" value="{{ old('db_username') }}" required autocomplete="off" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('db_username') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Database Password</label>
                            <input type="password" name="db_password" autocomplete="new-password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('db_password') border-red-500 @enderror">
                        </div>
                    </div>
                </div>

                <!-- Account Setup -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-base font-semibold text-slate-900">{{ __('app.first_account') }}</h3>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" required dir="auto" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('company_name') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required dir="auto" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('name') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('email') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                            <input type="password" name="password" required minlength="12" autocomplete="new-password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('password') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border">
                        </div>
                        <div class="grid grid-cols-2 gap-4 md:col-span-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Currency Code</label>
                                <input type="text" name="currency_code" value="{{ old('currency_code', 'USD') }}" required maxlength="3" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('currency_code') border-red-500 @enderror">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Decimal Places</label>
                                <select name="minor_units" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border">
                                    @foreach([0,2,3] as $units)
                                        <option value="{{ $units }}" @selected((int) old('minor_units',2)===$units)>{{ $units }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Timezone</label>
                            <input type="text" name="timezone" value="{{ old('timezone', 'UTC') }}" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('timezone') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Language</label>
                            <select name="locale" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border">
                                @foreach(config('product.locales') as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Mail Setup -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-base font-semibold text-slate-900">{{ __('app.mail_setup') }}</h3>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Mail Delivery</label>
                            <select name="mail_mailer" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border">
                                <option value="log" @selected(old('mail_mailer')==='log')>{{ __('app.mail_log') }}</option>
                                <option value="smtp" @selected(old('mail_mailer')==='smtp')>{{ __('app.mail_smtp') }}</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">{{ __('app.mail_helper') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Mail Host</label>
                            <input type="text" name="mail_host" value="{{ old('mail_host') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('mail_host') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Mail Port</label>
                            <input type="number" name="mail_port" value="{{ old('mail_port', '587') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('mail_port') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Mail Username</label>
                            <input type="text" name="mail_username" value="{{ old('mail_username') }}" autocomplete="off" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('mail_username') border-red-500 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Mail Password</label>
                            <input type="password" name="mail_password" autocomplete="new-password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @error('mail_password') border-red-500 @enderror">
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-blue-50 p-4 border border-blue-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm text-blue-800">{{ __('app.key_warning') }}</p>
                </div>

                <button type="submit" 
                        class="w-full flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-6 py-4 text-base font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all hover:bg-indigo-700 hover:shadow-indigo-600/40 focus:outline-none focus:ring-4 focus:ring-indigo-200 disabled:opacity-70 disabled:cursor-wait"
                        @disabled(in_array(false,$checks,true)) 
                        :disabled="submitting">
                    <svg x-show="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-show="!submitting">{{ __('app.complete_install') }}</span>
                    <span x-show="submitting" x-cloak>{{ __('app.installing') }}...</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
