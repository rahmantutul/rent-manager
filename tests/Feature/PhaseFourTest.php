<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Building;
use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\OwnerBootstrap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseFourTest extends TestCase
{
    use RefreshDatabase;

    private function staff(Role $role = Role::Owner): User
    {
        $u = User::factory()->create(['role' => $role]);
        app(OwnerBootstrap::class)->run($u->owner);

        return $u;
    }

    private function unit(User $u): Unit
    {
        $this->actingAs($u);

        return Building::factory()->create()->units()->create(['unit_number' => '101', 'floor_level' => 1, 'unit_type' => 'apartment', 'default_rent' => 120000, 'status' => 'vacant']);
    }

    public function test_tenant_crud_and_encryption(): void
    {
        $u = $this->staff();
        $this->actingAs($u);
        $this->post('/admin/tenants', ['type' => 'individual', 'name' => 'Alice', 'email' => 'alice@test.test', 'status' => 'prospect', 'id_document_number' => 'SECRET-123'])->assertRedirect();
        $t = Tenant::first();
        $this->assertNotSame('SECRET-123',
            DB::table('tenants')->value('id_document_number'));
        $this->get('/admin/tenants/'.$t->id)->assertOk()->assertSee('Alice');
    }

    public function test_activation_double_active_and_termination(): void
    {
        $u = $this->staff();
        $unit = $this->unit($u);
        $tenant = Tenant::factory()->create(['name' => 'A']);
        $this->actingAs($u);
        $this->post('/admin/leases', ['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'start_date' => '2026-01-01', 'billing_cycle' => 'monthly', 'billing_day' => 1, 'due_after_days' => 0, 'grace_days' => 0, 'security_deposit' => 0])->assertRedirect();
        $lease = Lease::first();
        $this->post('/admin/leases/'.$lease->id.'/activate')->assertRedirect();
        $this->assertSame('occupied', $unit->fresh()->status);
        $tenant2 = Tenant::factory()->create(['name' => 'B']);
        $second = Lease::factory()->create(['unit_id' => $unit->id, 'tenant_id' => $tenant2->id]);
        $this->post('/admin/leases/'.$second->id.'/activate')->assertSessionHasErrors('lease');
        $this->post('/admin/leases/'.$lease->id.'/terminate', ['move_out_date' => '2026-02-01'])->assertRedirect();
        $this->assertSame('vacant', $unit->fresh()->status);
        $this->assertSame('terminated', $lease->fresh()->status);
    }

    public function test_change_rent_keeps_history_and_private_document(): void
    {
        $u = $this->staff();
        $unit = $this->unit($u);
        $tenant = Tenant::factory()->create();
        $this->actingAs($u);
        $this->post('/admin/leases', ['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'start_date' => '2026-01-01', 'billing_cycle' => 'monthly', 'billing_day' => 1, 'due_after_days' => 0, 'grace_days' => 0, 'security_deposit' => 0]);
        $lease = Lease::first();
        $this->post('/admin/leases/'.$lease->id.'/change-rent', ['amount' => 130000, 'starts_on' => '2026-02-01'])->assertRedirect();
        $this->assertSame(2, $lease->charges()->count());
        $this->assertSame('2026-01-31', $lease->charges()->oldest('starts_on')->first()->ends_on->toDateString());
        Storage::fake('local');
        $file = UploadedFile::fake()->create('id.pdf', 100, 'application/pdf');
        $this->post('/admin/tenants/'.$tenant->id.'/document', ['document' => $file])->assertRedirect();
        $attachment = $tenant->attachments()->first();
        $this->get('/attachments/'.$attachment->id)->assertOk();
        $this->actingAs(User::factory()->create(['role' => Role::Owner]))->get('/attachments/'.$attachment->id)->assertForbidden();
    }

    public function test_accountant_read_only_and_owner_isolation(): void
    {
        $u = $this->staff();
        $this->actingAs($u);
        $tenant = Tenant::factory()->create();
        $accountant = User::factory()->create(['owner_id' => $u->owner_id, 'role' => Role::Accountant]);
        $this->actingAs($accountant)->get('/admin/tenants')->assertOk();
        $this->post('/admin/tenants', [])->assertForbidden();
        $other = $this->staff();
        $this->actingAs($other)->get('/admin/tenants/'.$tenant->id)->assertNotFound();
    }
}
