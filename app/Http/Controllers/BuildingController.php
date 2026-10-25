<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuildingRequest;
use App\Models\Building;
use App\Models\Property;

class BuildingController extends Controller
{
    public function create(Property $property)
    {
        $this->authorize('create', Building::class);

        return view('buildings.form', ['property' => $property, 'building' => new Building(['property_id' => $property->id])]);
    }

    public function store(BuildingRequest $request, Property $property)
    {
        $this->authorize('create', Building::class);
        $building = $property->buildings()->create($request->validated());

        return redirect()->route('admin.properties.show', $property)->with('status', __('app.building_created'));
    }

    public function edit(Building $building)
    {
        $this->authorize('update', $building);

        return view('buildings.form', ['property' => $building->property, 'building' => $building]);
    }

    public function update(BuildingRequest $request, Building $building)
    {
        $this->authorize('update', $building);
        $building->update($request->validated());

        return redirect()->route('admin.properties.show', $building->property)->with('status', __('app.saved'));
    }

    public function destroy(Building $building)
    {
        $this->authorize('delete', $building);
        $property = $building->property;
        $building->delete();

        return redirect()->route('admin.properties.show', $property)->with('status', __('app.deleted'));
    }
}
