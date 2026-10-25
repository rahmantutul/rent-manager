<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PDO;
use RuntimeException;
use Throwable;

class Installation
{
    public function installed(): bool
    {
        return is_file(config('install.lock_path'));
    }

    public function requirements(): array
    {
        $checks = ['php' => PHP_VERSION_ID >= 80200];
        foreach (['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'fileinfo', 'dom', 'session', 'filter', 'hash'] as $extension) {
            $checks[$extension] = extension_loaded($extension);
        }
        foreach (['storage', 'bootstrap/cache'] as $folder) {
            $checks[$folder] = is_writable(base_path($folder));
        }
        $checks['environment'] = is_file(config('install.env_path')) ? is_writable(config('install.env_path')) : is_writable(dirname(config('install.env_path')));

        return $checks;
    }

    public function connect(array $data): void
    {
        try {
            new PDO('mysql:host='.$data['db_host'].';port='.$data['db_port'].';dbname='.$data['db_database'].';charset=utf8mb4', $data['db_username'], $data['db_password'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        } catch (Throwable) {
            throw ValidationException::withMessages(['db_database' => __('app.database_failed')]);
        }
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => $data['db_host'], 'database.connections.mysql.port' => $data['db_port'], 'database.connections.mysql.database' => $data['db_database'], 'database.connections.mysql.username' => $data['db_username'], 'database.connections.mysql.password' => $data['db_password'] ?? '', 'database.connections.mysql.url' => null]);
        DB::purge('mysql');
    }

    public function run(array $data): void
    {
        $handle = fopen(config('install.mutex_path'), 'c');
        if ($handle === false) {
            throw new RuntimeException('Cannot open installation mutex.');
        }
        try {
            if (! flock($handle, LOCK_EX | LOCK_NB)) {
                throw ValidationException::withMessages(['install' => __('app.install_busy')]);
            }
            abort_if($this->installed(), 403);
            if (in_array(false, $this->requirements(), true)) {
                throw ValidationException::withMessages(['install' => __('app.requirements_failed')]);
            }
            $this->connect($data);
            // Never adopt an existing application's database or overwrite its users.
            $tables = Schema::getTableListing(Schema::getCurrentSchemaListing(), schemaQualified: false);
            $allowed = ['migrations', 'owners', 'users', 'settings', 'properties', 'buildings', 'units', 'charge_types', 'tax_rates', 'late_fee_rules', 'numbering_sequences', 'tenants', 'leases', 'lease_charges', 'lease_tenants', 'attachments', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];
            foreach ($tables as $table) {
                if (! in_array($table, $allowed, true)) {
                    throw ValidationException::withMessages(['db_database' => __('app.database_not_empty')]);
                }
            }
            foreach (['owners', 'users', 'settings'] as $table) {
                if (Schema::hasTable($table) && DB::table($table)->exists()) {
                    throw ValidationException::withMessages(['db_database' => __('app.database_not_empty')]);
                }
            }
            $this->writeEnvironment($data);
            if (Artisan::call('migrate', ['--force' => true]) !== 0) {
                throw new RuntimeException('Migration failed.');
            }
            $cache = base_path('bootstrap/cache/config.php');
            if (is_file($cache) && ! unlink($cache)) {
                throw new RuntimeException('Cannot remove stale configuration cache.');
            }
            try {
                DB::transaction(function () use ($data) {
                    $owner = new Owner;
                    $owner->fill(['name' => $data['company_name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? '', 'currency_code' => $data['currency_code'], 'minor_units' => $data['minor_units'], 'timezone' => $data['timezone'], 'locale' => $data['locale'], 'date_format' => 'Y-m-d']);
                    $owner->status = 'active';
                    $owner->save();
                    $user = new User;
                    $user->fill(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
                    $user->owner_id = $owner->id;
                    $user->role = Role::Owner;
                    $user->is_active = true;
                    $user->save();
                    app(OwnerBootstrap::class)->run($owner);
                    if (file_put_contents(config('install.lock_path'), date(DATE_ATOM), LOCK_EX) === false) {
                        throw new RuntimeException('Cannot write install lock.');
                    }
                });
            } catch (Throwable $e) {
                if ($this->installed()) {
                    unlink(config('install.lock_path'));
                }
                throw $e;
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function writeEnvironment(array $data): void
    {
        $path = config('install.env_path');
        $contents = is_file($path) ? file_get_contents($path) : file_get_contents(base_path('.env.example'));
        $values = ['APP_NAME' => config('app.product_name'), 'APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => $data['app_url'], 'APP_KEY' => config('app.key'), 'DB_CONNECTION' => 'mysql', 'DB_URL' => '', 'DB_HOST' => $data['db_host'], 'DB_PORT' => $data['db_port'], 'DB_DATABASE' => $data['db_database'], 'DB_USERNAME' => $data['db_username'], 'DB_PASSWORD' => $data['db_password'] ?? '', 'SESSION_DRIVER' => 'file', 'CACHE_STORE' => 'file', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => $data['mail_mailer'], 'MAIL_HOST' => $data['mail_host'] ?? '', 'MAIL_PORT' => $data['mail_port'] ?? 587, 'MAIL_USERNAME' => $data['mail_username'] ?? '', 'MAIL_PASSWORD' => $data['mail_password'] ?? '', 'MAIL_SCHEME' => $data['mail_scheme'] ?? 'smtp', 'MAIL_FROM_ADDRESS' => $data['email'], 'MAIL_FROM_NAME' => $data['company_name']];
        foreach ($values as $key => $value) {
            $line = $key.'="'.str_replace(['\\', '"', '$', "\r", "\n"], ['\\\\', '\\"', '\\$', '', ''], (string) $value).'"';
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            $contents = preg_match($pattern, $contents) ? preg_replace_callback($pattern, fn () => $line, $contents) : $contents."\n".$line;
        }
        $temporary = $path.'.install-tmp';
        if (file_put_contents($temporary, $contents, LOCK_EX) === false || ! rename($temporary, $path)) {
            throw new RuntimeException('Cannot save environment.');
        }
    }
}
