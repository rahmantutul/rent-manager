<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Building;
use App\Models\ChargeType;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\User;
use App\Services\InvoiceGenerationService;
use App\Services\OwnerBootstrap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFiveTest extends TestCase
{
    use RefreshDatabase;

    private function setupLease(string $start = '2026-01-15', bool $prorate = true): array
    {
        $u = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($u);
        app(OwnerBootstrap::class)->run($u->owner);
        $unit = Building::factory()->create()->units()->create(['unit_number' => '101', 'floor_level' => 1, 'unit_type' => 'apartment', 'default_rent' => 120000, 'status' => 'occupied']);
        $tenant = Tenant::factory()->create();
        $lease = Lease::create(['owner_id' => $u->owner_id, 'lease_number' => 'L-1', 'unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'start_date' => $start, 'status' => 'active', 'active_unit_id' => $unit->id, 'billing_cycle' => 'monthly', 'billing_day' => 1, 'due_after_days' => 0, 'grace_days' => 0, 'next_invoice_date' => $start, 'auto_invoice' => true, 'prorate_first_period' => $prorate, 'security_deposit' => 0]);
        $rent = ChargeType::where('name', 'Rent')->first();
        $lease->charges()->create(['owner_id' => $u->owner_id, 'charge_type_id' => $rent->id, 'amount' => 120000, 'frequency' => 'every_cycle', 'starts_on' => $start, 'is_active' => true]);

        return [$u, $lease];
    }

    public function test_first_prorated_period_and_idempotency(): void
    {
        [$u,$lease] = $this->setupLease();
        $result = app(InvoiceGenerationService::class)->generate('2026-01-15');
        $this->assertCount(1, $result['created'], json_encode($result['errors']));
        $this->assertSame(65806, Invoice::first()->total);
        $this->assertSame(1, Invoice::count());
        $result = app(InvoiceGenerationService::class)->generate('2026-01-15');
        $this->assertCount(0, $result['created']);
    }

    public function test_catch_up_and_termination_end_date(): void
    {
        [$u,$lease] = $this->setupLease('2026-01-01', false);
        $lease->update(['end_date' => '2026-03-10']);
        $result = app(InvoiceGenerationService::class)->generate('2026-05-01');
        $this->assertCount(3, $result['created'], json_encode($result['errors']));
        $this->assertSame([120000, 120000, 38710], Invoice::orderBy('id')->pluck('total')->all());
        $this->assertNull($lease->fresh()->next_invoice_date);
    }

    public function test_exclusive_and_inclusive_tax_snapshots(): void
    {
        [$u, $lease] = $this->setupLease('2026-01-01', false);
        $rate = TaxRate::create(['name' => 'VAT', 'rate' => 5, 'is_inclusive' => false, 'is_active' => true]);
        $lease->charges()->first()->update(['amount' => 100000, 'tax_rate_id' => $rate->id]);
        app(InvoiceGenerationService::class)->generate('2026-01-01');
        $this->assertSame(5000, Invoice::first()->tax_total);

        [$u, $lease] = $this->setupLease('2026-02-01', false);
        $rate = TaxRate::create(['name' => 'VAT inclusive', 'rate' => 5, 'is_inclusive' => true, 'is_active' => true]);
        $lease->charges()->first()->update(['amount' => 105000, 'tax_rate_id' => $rate->id]);
        app(InvoiceGenerationService::class)->generate('2026-02-01');
        $latest = Invoice::latest('id')->first();
        $this->assertSame(5000, $latest->tax_total);
        $this->assertSame(100000, $latest->subtotal);
    }
}
