<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Building;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Policies\BuildingPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\UnitPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->app->runningUnitTests() && ! is_file(config('install.lock_path'))) {
            config(['session.driver' => 'file', 'cache.default' => 'file', 'queue.default' => 'sync']);
            if (! config('app.key')) {
                $path = storage_path('framework/install.key');
                $handle = fopen($path, 'c+');
                if ($handle === false) {
                    throw new \RuntimeException('Installer storage must be writable.');
                }
                try {
                    flock($handle, LOCK_EX);
                    $key = stream_get_contents($handle);
                    if (! $key) {
                        $key = 'base64:'.base64_encode(random_bytes(32));
                        fwrite($handle, $key);
                        fflush($handle);
                    }
                    config(['app.key' => $key]);
                } finally {
                    flock($handle, LOCK_UN);
                    fclose($handle);
                }
            }
        }
    }

    public function boot(): void
    {
        Blade::directive('money', fn ($expression) => "<?= e(money($expression)) ?>");
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Building::class, BuildingPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::define('manage-owner-settings', fn (User $user) => $user->canSignIn() && $user->hasRole(Role::Owner));
        Gate::define('manage-global-branding', fn (User $user) => $user->canSignIn() && $user->hasRole(Role::SuperAdmin));
    }
}
