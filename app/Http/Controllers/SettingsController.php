<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandingRequest;
use App\Http\Requests\LocaleRequest;
use App\Http\Requests\SettingsRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit()
    {
        Gate::authorize('manage-owner-settings');

        return view('settings.owner', ['owner' => auth()->user()->owner, 'address' => Setting::where('key', 'company_address')->value('value')]);
    }

    public function update(SettingsRequest $request)
    {
        $owner = $request->user()->owner;
        $path = $request->file('logo')?->store('logos', 'local');
        $previous = $owner->logo_path;
        try {
            DB::transaction(function () use ($request, $owner, $path) {
                $data = $request->safe()->except(['logo', 'company_address']);
                $data['phone'] = $data['phone'] ?? '';
                if ($path) {
                    $data['logo_path'] = $path;
                }
                $owner->update($data);
                Setting::updateOrCreate(['key' => 'company_address'], ['value' => $request->validated('company_address') ?? '']);
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            } throw $e;
        }
        if ($path && $previous) {
            Storage::disk('local')->delete($previous);
        }

        return back()->with('status', __('app.saved'));
    }

    public function branding()
    {
        Gate::authorize('manage-global-branding');

        return view('settings.branding');
    }

    public function updateBranding(BrandingRequest $request)
    {
        $handle = fopen(storage_path('framework/branding.mutex'), 'c');
        if ($handle === false) {
            throw new \RuntimeException('Cannot open branding mutex.');
        }
        $path = null;
        try {
            flock($handle, LOCK_EX);
            $path = $request->file('logo')?->store('logos', 'local');
            $old = DB::table('settings')->whereNull('owner_id')->where('key', 'logo_path')->value('value');
            DB::transaction(function () use ($request, $path) {
                $values = $request->safe()->except('logo');
                if ($path) {
                    $values['logo_path'] = $path;
                }
                foreach ($values as $key => $value) {
                    DB::table('settings')->updateOrInsert(['owner_id' => null, 'key' => $key], ['value' => $value]);
                }
            });
            if ($path && $old) {
                Storage::disk('local')->delete($old);
            }
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            } throw $e;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        return back()->with('status', __('app.saved'));
    }

    public function locale(LocaleRequest $request)
    {
        $request->user()->update($request->validated());

        return back();
    }

    public function logo(string $scope)
    {
        if ($scope === 'global') {
            // Global branding is intentionally visible on the public login screen.
            $path = config('product.logo_path');
        } else {
            abort_unless($scope === 'owner' && auth()->user()?->canSignIn() && auth()->user()?->owner, 403);
            $path = auth()->user()->owner->logo_path;
        }
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
