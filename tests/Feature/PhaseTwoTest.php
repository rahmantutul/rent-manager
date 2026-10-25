<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Building;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    private function ownerUser(Role $role = Role::Owner): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_property_crud_creates_default_building(): void
    {
        $user = $this->ownerUser();
        $this->actingAs($user);
        $property = Property::factory()->create(['name' => 'Main Estate', 'code' => 'MAIN']);
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'owner_id' => $user->owner_id]);
        $this->assertDatabaseHas('buildings', ['property_id' => $property->id, 'name' => 'Main Estate']);
        $this->get('/admin/properties')->assertOk()->assertSee('Main Estate');
        $this->get('/admin/properties/'.$property->id)->assertOk();
        $this->put('/admin/properties/'.$property->id, ['name' => 'Updated Estate', 'code' => 'UPDATED', 'type' => 'commercial', 'address_line1' => '1 Road', 'city' => 'Dhaka', 'postal_code' => '1200', 'country_code' => 'BD'])->assertRedirect();
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'name' => 'Updated Estate']);
        $this->delete('/admin/properties/'.$property->id)->assertRedirect();
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_building_and_unit_crud(): void
    {
        $bufferLevel = ob_get_level();

        try {
            $this->actingAs($user = $this->ownerUser());
            $property = Property::factory()->create();
            $building = $property->buildings()->create(['name' => 'Block B', 'floors_count' => 10]);
            $unit = $building->units()->create(['unit_number' => '3A', 'floor_level' => 3, 'unit_type' => 'apartment', 'default_rent' => 120000, 'status' => 'vacant']);
            $this->assertSame('Floor 3', $unit->fresh()->floor_label);
            $this->get('/admin/buildings/'.$building->id.'/units')->assertOk()->assertSee('3A')->assertSee('Floor 3');
            $this->put('/admin/units/'.$unit->id, ['building_id' => $building->id, 'unit_number' => '3A', 'floor_level' => 3, 'unit_type' => 'apartment', 'default_rent' => 130000, 'status' => 'maintenance'])->assertRedirect();
            $this->assertDatabaseHas('units', ['id' => $unit->id, 'status' => 'maintenance', 'default_rent' => 130000]);
            $this->delete('/admin/units/'.$unit->id)->assertRedirect();
            $this->assertSoftDeleted('units', ['id' => $unit->id]);
            $this->assertSame($user->owner_id, $building->owner_id);
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }
    }

    public function test_bulk_generation_numbers_counts_and_clashes_are_atomic(): void
    {
        $this->actingAs($this->ownerUser());
        $building = Building::factory()->create();
        $this->post('/admin/buildings/'.$building->id.'/units/bulk', ['from_floor' => 1, 'to_floor' => 3, 'units_per_floor' => 4, 'pattern' => 'floor_letter', 'unit_type' => 'apartment', 'default_rent' => 120000])->assertRedirect();
        $this->assertSame(12, $building->units()->count());
        $this->assertSame(['1A', '1B', '1C', '1D', '2A', '2B', '2C', '2D', '3A', '3B', '3C', '3D'], $building->units()->orderBy('unit_number')->pluck('unit_number')->all());
        $this->post('/admin/buildings/'.$building->id.'/units/bulk', ['from_floor' => 3, 'to_floor' => 4, 'units_per_floor' => 2, 'pattern' => 'floor_letter', 'unit_type' => 'apartment', 'default_rent' => 120000])->assertSessionHasErrors('clashes');
        $this->assertSame(12, $building->units()->count());
        $this->post('/admin/buildings/'.$building->id.'/units/bulk', ['from_floor' => 4, 'to_floor' => 4, 'units_per_floor' => 2, 'pattern' => 'floor_number', 'unit_type' => 'shop', 'default_rent' => 50000])->assertRedirect();
        $this->assertDatabaseHas('units', ['building_id' => $building->id, 'unit_number' => '401', 'floor_level' => 4]);
    }

    public function test_floor_labels_and_owner_isolation(): void
    {
        $first = $this->ownerUser();
        $this->actingAs($first);
        $firstProperty = Property::factory()->create(['name' => 'Private A']);
        $building = $firstProperty->buildings()->first();
        $ground = $building->units()->create(['unit_number' => 'G', 'floor_level' => 0, 'unit_type' => 'storage', 'default_rent' => 0, 'status' => 'reserved']);
        $basement = $building->units()->create(['unit_number' => 'B1', 'floor_level' => -1, 'unit_type' => 'parking', 'default_rent' => 0, 'status' => 'vacant']);
        $this->assertSame('Ground floor', $ground->floor_label);
        $this->assertSame('Basement 1', $basement->floor_label);
        $second = $this->ownerUser();
        $this->actingAs($second);
        $this->get('/admin/properties/'.$firstProperty->id)->assertNotFound();
        $this->get('/admin/buildings/'.$building->id.'/units')->assertNotFound();
        $this->assertSame(0, Property::count());
    }

    public function test_accountant_is_read_only_and_tenant_has_no_access(): void
    {
        $owner = $this->ownerUser();
        $this->actingAs($owner);
        $property = Property::factory()->create();
        $accountant = User::factory()->create(['owner_id' => $owner->owner_id, 'role' => Role::Accountant]);
        $this->actingAs($accountant)->get('/admin/properties')->assertOk();
        $this->post('/admin/properties', [])->assertForbidden();
        $this->put('/admin/properties/'.$property->id, [])->assertForbidden();
        $tenant = User::factory()->create(['role' => Role::Tenant]);
        $this->actingAs($tenant)->get('/admin/properties')->assertForbidden();
    }
}
