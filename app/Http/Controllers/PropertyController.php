<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Models\Property;

class PropertyController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Property::class);
        $properties = Property::withCount('buildings')->withCount(['buildings as unit_count' => fn ($query) => $query->join('units', 'buildings.id', '=', 'units.building_id')->selectRaw('count(units.id)')])->withCount(['buildings as vacant_count' => fn ($query) => $query->join('units', 'buildings.id', '=', 'units.building_id')->where('units.status', 'vacant')->selectRaw('count(units.id)')])->when(request('search'), fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))->latest('id')->paginate(15)->withQueryString();

        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        $this->authorize('create', Property::class);

        return view('properties.form', ['property' => new Property]);
    }

    public function store(PropertyRequest $request)
    {
        $this->authorize('create', Property::class);
        $property = Property::create($request->validated());

        return redirect()->route('admin.properties.show', $property)->with('status', __('app.property_created'));
    }

    public function show(Property $property)
    {
        $this->authorize('view', $property);

        return view('properties.show', ['property' => $property->load('buildings')]);
    }

    public function edit(Property $property)
    {
        $this->authorize('update', $property);

        return view('properties.form', compact('property'));
    }

    public function update(PropertyRequest $request, Property $property)
    {
        $this->authorize('update', $property);
        $property->update($request->validated());

        return redirect()->route('admin.properties.show', $property)->with('status', __('app.saved'));
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);
        $property->delete();

        return redirect()->route('admin.properties.index')->with('status', __('app.deleted'));
    }
}
