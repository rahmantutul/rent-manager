<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()?->canSignIn()) {
            return redirect()->route($request->user()->role->landingRoute());
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $key = hash('sha256', strtolower($request->string('email')).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => __('app.throttled')]);
        }
        if (! Auth::attempt($request->validated()) || ! Auth::user()->canSignIn()) {
            Auth::logout();
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => __('app.login_failed')]);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->route($request->user()->role->landingRoute());
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
