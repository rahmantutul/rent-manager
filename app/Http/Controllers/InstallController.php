<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstallRequest;
use App\Services\Installation;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class InstallController extends Controller
{
    public function show(Installation $installation)
    {
        abort_if($installation->installed(), 403);

        return view('install', ['checks' => $installation->requirements()]);
    }

    public function store(InstallRequest $request, Installation $installation)
    {
        try {
            $installation->run($request->validated());
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('Installation failed', ['exception_type' => get_class($e)]);

            return back()->withErrors(['install' => __('app.install_failed')])->withInput($request->except(['password', 'password_confirmation', 'db_password', 'mail_password']));
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', __('app.installed'));
    }
}
