<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUnitRequest;
use App\Http\Requests\UnitRequest;
use App\Models\Building;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function index(Building $building)
    {
        $this->authorize('view', $building);
        $units = $building->units()->when(request('status'), fn ($q, $v) => $q->where('status', $v))->when(request('floor') !== null && request('floor') !== '', fn ($q) => $q->where('floor_level', request('floor')))->when(request('unit_type'), fn ($q, $v) => $q->where('unit_type', $v))->orderBy('floor_level')->orderBy('unit_number')->get()->groupBy('floor_label');

        return view('units.index', compact('building', 'units'));
    }

    public function create(Building $building)
    {
        $this->authorize('create', Unit::class);

        return view('units.form', ['building' => $building, 'unit' => new Unit(['building_id' => $building->id, 'status' => 'vacant', 'default_rent' => 0])]);
    }

    public function store(UnitRequest $request, Building $building)
    {
        $this->authorize('create', Unit::class);
        $building->units()->create($request->validated());

        return redirect()->route('admin.buildings.units.index', $building)->with('status', __('app.unit_created'));
    }

    public function edit(Unit $unit)
    {
        $this->authorize('update', $unit);

        return view('units.form', ['building' => $unit->building, 'unit' => $unit]);
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $this->authorize('update', $unit);
        $unit->update($request->validated());

        return redirect()->route('admin.buildings.units.index', $unit->building)->with('status', __('app.saved'));
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit);
        $building = $unit->building;
        $unit->delete();

        return redirect()->route('admin.buildings.units.index', $building)->with('status', __('app.deleted'));
    }

    public function bulk(Building $building)
    {
        $this->authorize('create', Unit::class);

        return view('units.bulk', compact('building'));
    }

    public function generate(BulkUnitRequest $request, Building $building)
    {
        $this->authorize('create', Unit::class);
        $data = $request->validated();
        $numbers = [];
        $floorByNumber = [];
        for ($floor = $data['from_floor']; $floor <= $data['to_floor']; $floor++) {
            for ($index = 1; $index <= $data['units_per_floor']; $index++) {
                $number = $data['pattern'] === 'floor_letter' ? $floor.chr(64 + $index) : (string) ($floor * 100 + $index);
                $numbers[] = $number;
                $floorByNumber[$number] = $floor;
            }
        }
        $existing = $building->units()->whereIn('unit_number', $numbers)->pluck('unit_number')->all();
        if ($existing) {
            return back()->withErrors(['clashes' => __('app.bulk_clashes', ['numbers' => implode(', ', $existing)])])->withInput();
        }
        DB::transaction(function () use ($building, $data, $numbers, $floorByNumber): void {
            foreach ($numbers as $number) {
                $building->units()->create(['unit_number' => $number, 'floor_level' => $floorByNumber[$number], 'unit_type' => $data['unit_type'], 'default_rent' => $data['default_rent'], 'status' => 'vacant']);
            }
        });

        return redirect()->route('admin.buildings.units.index', $building)->with('status', __('app.bulk_created', ['count' => count($numbers)]));
    }
}
