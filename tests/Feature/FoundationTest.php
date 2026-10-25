<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Owner;
use App\Models\Setting;
use App\Models\User;
use App\Services\Installation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    private string $temporary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->temporary = sys_get_temp_dir().'/rent-foundation-'.bin2hex(random_bytes(8));
        mkdir($this->temporary);
        config(['install.lock_path' => $this->temporary.'/installed.lock', 'install.mutex_path' => $this->temporary.'/mutex', 'install.env_path' => $this->temporary.'/.env']);
        file_put_contents(config('install.lock_path'), 'installed');
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->temporary.'/*') as $path) {
            unlink($path);
        }
        foreach (glob($this->temporary.'/.*') as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        rmdir($this->temporary);
        parent::tearDown();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (['/admin', '/admin/settings', '/portal', '/super-admin'] as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }
    }

    public static function roles(): array
    {
        return [['owner', '/admin'], ['manager', '/admin'], ['accountant', '/admin'], ['tenant', '/portal'], ['super_admin', '/super-admin']];
    }

    #[DataProvider('roles')]
    public function test_roles_land_in_their_own_area(string $role, string $destination): void
    {
        $user = User::factory()->create(['role' => $role, ...($role === 'super_admin' ? ['owner_id' => null] : [])]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(url($destination));
        $this->assertAuthenticatedAs($user);
        $this->get($destination)->assertOk();
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_tenant_cannot_enter_admin_even_with_an_intended_url(): void
    {
        $user = User::factory()->create(['role' => Role::Tenant]);
        $this->withSession(['url.intended' => url('/admin')])->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('portal.dashboard'));
        $this->get('/admin')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
        $this->get('/super-admin')->assertForbidden();
    }

    public function test_logout_and_inactive_accounts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
        $user->forceFill(['is_active' => false])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $user->forceFill(['is_active' => true])->save();
        $user->owner->forceFill(['status' => 'suspended'])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->actingAs($user->fresh())->get('/admin')->assertForbidden();
    }

    public function test_login_is_throttled(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', ['email' => 'invalid@example.com', 'password' => 'wrong']);
        }
        $response->assertSessionHasErrors(['email' => __('app.throttled')]);
    }

    public function test_owner_scope_isolates_records_and_fails_closed_for_guests(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $this->actingAs($first);
        $setting = Setting::create(['key' => 'company_address', 'value' => 'Private address']);
        $this->assertSame($first->owner_id, $setting->owner_id);
        $this->actingAs($second);
        $this->assertNull(Setting::find($setting->id));
        $this->assertSame(0, Setting::count());
        $this->post('/logout');
        $this->assertSame(0, Setting::count());
    }

    public function test_owner_cannot_create_or_reassign_another_owners_record(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $setting = new Setting(['key' => 'test', 'value' => 'test']);
        $setting->owner_id = Owner::factory()->create()->id;
        $this->expectException(\LogicException::class);
        $setting->save();
    }

    private function settings(array $overrides = []): array
    {
        return array_replace(['name' => 'Example Company', 'email' => 'owner@example.com', 'phone' => '', 'company_address' => 'Road 1', 'currency_code' => 'JPY', 'minor_units' => 0, 'timezone' => 'Asia/Dhaka', 'locale' => 'en', 'date_format' => 'd/m/Y'], $overrides);
    }

    public function test_owner_can_save_settings_and_private_logo_without_mutating_another_owner(): void
    {
        Storage::fake('local');
        $first = User::factory()->create();
        $second = User::factory()->create();
        $original = $second->owner->toArray();
        $logo = UploadedFile::fake()->image('logo.png');
        $this->actingAs($first)->put('/admin/settings', $this->settings(['logo' => $logo, 'owner_id' => $second->owner_id, 'role' => 'super_admin', 'product_name' => 'Forged name']))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('JPY', $first->owner->fresh()->currency_code);
        $this->assertSame(0, $first->owner->fresh()->minor_units);
        $this->assertSame($original, $second->owner->fresh()->toArray());
        $this->assertDatabaseMissing('settings', ['key' => 'product_name']);
        $this->get('/branding/owner')->assertOk();
        $path = $first->owner->fresh()->logo_path;
        Storage::disk('local')->assertExists($path);
        $this->actingAs($second)->get('/branding/owner')->assertNotFound();
        $this->get('/admin/settings')->assertOk()->assertDontSee('Road 1');
        $this->get('/storage/'.$path)->assertNotFound();
    }

    public function test_invalid_settings_and_logo_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())->put('/admin/settings', $this->settings(['currency_code' => 'INVALID', 'minor_units' => 1, 'timezone' => 'wrong', 'locale' => 'xx', 'logo' => UploadedFile::fake()->create('logo.svg', 1, 'image/svg+xml')]))->assertSessionHasErrors(['currency_code', 'minor_units', 'timezone', 'locale', 'logo']);
    }

    public function test_settings_and_global_branding_permissions(): void
    {
        foreach ([Role::Manager, Role::Accountant, Role::Tenant] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get('/admin/settings')->assertForbidden();
            $this->put('/admin/settings', $this->settings())->assertForbidden();
        }
        $this->actingAs(User::factory()->create())->put('/super-admin/branding', ['product_name' => 'No', 'primary_color' => '#123456'])->assertForbidden();
        $this->actingAs(User::factory()->superAdmin()->create())->get('/admin')->assertForbidden();
    }

    public function test_super_admin_branding_updates_login_and_does_not_duplicate_null_owner_settings(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->superAdmin()->create());
        $this->put('/super-admin/branding', ['product_name' => 'My Rentals', 'primary_color' => '#123456', 'logo' => UploadedFile::fake()->image('brand.png')])->assertSessionHasNoErrors();
        $this->put('/super-admin/branding', ['product_name' => 'My Rentals', 'primary_color' => '#234567'])->assertSessionHasNoErrors();
        $this->assertSame(3, DB::table('settings')->whereNull('owner_id')->count());
        $this->get('/super-admin')->assertSee('My Rentals');
        $this->post('/logout');
        $this->get('/login')->assertOk()->assertSee('My Rentals')->assertSee('#234567');
        $this->get('/branding/global')->assertOk();
        $this->get('/branding/owner')->assertForbidden();
    }

    public function test_installer_is_locked_for_get_and_post(): void
    {
        $this->get('/install')->assertForbidden();
        $this->post('/install', [])->assertForbidden();
    }

    public function test_uninstalled_app_routes_to_requirements(): void
    {
        unlink(config('install.lock_path'));
        $this->get('/admin')->assertRedirect(route('install.show'));
        $this->get('/install')->assertOk()->assertSee(PHP_VERSION)->assertSee('bootstrap/cache');
    }

    private function installData(): array
    {
        return ['app_url' => 'http://localhost', 'db_host' => '127.0.0.1', 'db_port' => 3306, 'db_database' => 'rent_test', 'db_username' => 'root', 'db_password' => '', 'company_name' => 'Building Company', 'name' => 'First Owner', 'email' => 'first@example.com', 'password' => 'Strong-test-password1', 'password_confirmation' => 'Strong-test-password1', 'currency_code' => 'USD', 'minor_units' => 2, 'timezone' => 'UTC', 'locale' => 'en', 'mail_mailer' => 'log'];
    }

    public function test_installation_creates_owner_and_account_then_locks(): void
    {
        unlink(config('install.lock_path'));
        // Exercise the real install pipeline with only the MySQL connection replaced by SQLite.
        $service = \Mockery::mock(Installation::class)->makePartial();
        $service->shouldReceive('connect')->once()->andReturnNull();
        $this->app->instance(Installation::class, $service);
        $this->post('/install', $this->installData())->assertSessionHasNoErrors()->assertRedirect(route('login'));
        $this->assertFileExists(config('install.lock_path'));
        $this->assertFileExists(config('install.env_path'));
        $user = User::firstOrFail();
        $this->assertSame(Role::Owner, $user->role);
        $this->assertSame('Building Company', $user->owner->name);
        $this->assertTrue(Hash::check('Strong-test-password1', $user->password));
        $this->get('/install')->assertForbidden();
        $this->post('/install', $this->installData())->assertForbidden();
        $this->assertSame(1, User::count());
    }

    public function test_failed_database_connection_does_not_write_environment_or_flash_secrets(): void
    {
        unlink(config('install.lock_path'));
        file_put_contents(config('install.env_path'), 'SENTINEL=unchanged');
        $this->post('/install', array_replace($this->installData(), ['db_port' => 1, 'db_password' => 'never-flash-this', 'mail_password' => 'never-mail-this']))->assertSessionHasErrors('db_database');
        $this->assertSame('SENTINEL=unchanged', file_get_contents(config('install.env_path')));
        $this->assertFileDoesNotExist(config('install.lock_path'));
        $this->assertNull(session('_old_input.db_password'));
        $this->assertNull(session('_old_input.mail_password'));
    }

    public function test_schema_matches_phase_one_model(): void
    {
        $this->assertSame(['id', 'name', 'email', 'phone', 'currency_code', 'minor_units', 'timezone', 'locale', 'date_format', 'logo_path', 'status'], Schema::getColumnListing('owners'));
        $this->assertSame(['id', 'owner_id', 'tenant_id', 'name', 'email', 'password', 'role', 'permissions', 'locale', 'is_active', 'last_login_at'], Schema::getColumnListing('users'));
        $this->assertSame(['id', 'owner_id', 'key', 'value'], Schema::getColumnListing('settings'));
        $this->assertSame(['owner_id'], array_merge(...array_column(Schema::getForeignKeys('users'), 'columns')));
    }

    public function test_existing_database_is_rejected_without_environment_mutation(): void
    {
        unlink(config('install.lock_path'));
        User::factory()->create();
        $service = \Mockery::mock(Installation::class)->makePartial();
        $service->shouldReceive('connect')->once()->andReturnNull();
        $this->app->instance(Installation::class, $service);
        $this->post('/install', $this->installData())->assertSessionHasErrors('db_database');
        $this->assertFileDoesNotExist(config('install.env_path'));
        $this->assertSame(1, User::count());
    }

    public function test_owner_reassignment_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $setting = Setting::create(['key' => 'company_address', 'value' => 'Original']);
        $setting->owner_id = Owner::factory()->create()->id;
        $this->expectException(\LogicException::class);
        $setting->save();
    }

    public function test_locale_switcher_accepts_only_installed_languages(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/locale', ['locale' => 'en'])->assertSessionHasNoErrors();
        $this->assertSame('en', $user->fresh()->locale);
        $this->post('/locale', ['locale' => 'unsupported'])->assertSessionHasErrors('locale');
    }
}
