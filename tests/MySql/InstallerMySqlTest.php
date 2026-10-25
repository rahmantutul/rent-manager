<?php

namespace Tests\MySql;

use App\Enums\Role;
use App\Models\User;
use App\Services\Installation;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

#[Group('mysql')]
class InstallerMySqlTest extends TestCase
{
    public function test_real_mysql_installation_schema_environment_and_lock(): void
    {
        $host = getenv('MYSQL_TEST_HOST') ?: '127.0.0.1';
        $port = getenv('MYSQL_TEST_PORT') ?: '3306';
        $username = getenv('MYSQL_TEST_USERNAME') ?: 'root';
        $password = getenv('MYSQL_TEST_PASSWORD') ?: '';
        $database = 'rent_phase1_test_'.bin2hex(random_bytes(6));
        $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $folder = sys_get_temp_dir().'/'.$database;
        mkdir($folder);
        config(['install.lock_path' => $folder.'/installed.lock', 'install.mutex_path' => $folder.'/mutex', 'install.env_path' => $folder.'/.env']);
        try {
            app(Installation::class)->run([
                'app_url' => 'http://localhost', 'db_host' => $host, 'db_port' => $port,
                'db_database' => $database, 'db_username' => $username, 'db_password' => $password,
                'company_name' => 'Integration Test', 'name' => 'Test Owner', 'email' => 'mysql@example.test',
                'password' => 'Only-a-test-password!', 'currency_code' => 'JPY', 'minor_units' => 0,
                'timezone' => 'Asia/Dhaka', 'locale' => 'en', 'mail_mailer' => 'log',
                'mail_password' => 'synthetic $dollar \\ slash " quote # hash',
            ]);
            $this->assertSame(1, DB::table('owners')->count());
            $this->assertSame(Role::Owner, User::firstOrFail()->role);
            $this->assertSame(0, (int) DB::table('owners')->value('minor_units'));
            $this->assertFileExists($folder.'/installed.lock');
            $this->assertSame(['id', 'owner_id', 'key', 'value'], Schema::getColumnListing('settings'));
            $this->assertSame(['owner_id'], array_merge(...array_column(Schema::getForeignKeys('users'), 'columns')));
            $parsed = Dotenv::parse(file_get_contents($folder.'/.env'));
            $this->assertSame('synthetic $dollar \\ slash " quote # hash', $parsed['MAIL_PASSWORD']);
            $this->assertSame($database, $parsed['DB_DATABASE']);
            try {
                app(Installation::class)->run([]);
                $this->fail('A second installation must be rejected.');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
            $this->assertSame(1, User::count());
        } finally {
            DB::purge('mysql');
            // This random database was created by this test; no existing database is dropped.
            $pdo->exec("DROP DATABASE `{$database}`");
            foreach (['installed.lock', 'mutex', '.env', '.env.install-tmp'] as $name) {
                if (is_file($folder.'/'.$name)) {
                    unlink($folder.'/'.$name);
                }
            }
            rmdir($folder);
        }
    }
}
