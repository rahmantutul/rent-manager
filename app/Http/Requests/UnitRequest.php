<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->isMethod('post') ? 'create' : 'update', $this->isMethod('post') ? Unit::class : $this->route('unit'));
    }

    public function rules(): array
    {
        return ['building_id' => ['required', 'integer'], 'unit_number' => ['required', 'string', 'max:30'], 'floor_level' => ['required', 'integer', 'between:-100,500'], 'unit_type' => ['required', Rule::in(['apartment', 'shop', 'office', 'parking', 'storage', 'other'])], 'bedrooms' => ['nullable', 'integer', 'min:0', 'max:100'], 'bathrooms' => ['nullable', 'integer', 'min:0', 'max:100'], 'area' => ['nullable', 'numeric', 'min:0'], 'area_unit' => ['nullable', Rule::in(['sqft', 'sqm'])], 'default_rent' => ['required', 'integer', 'min:0'], 'status' => ['required', Rule::in(['vacant', 'maintenance', 'reserved'])], 'features' => ['nullable', 'array'], 'notes' => ['nullable', 'string']];
    }
}
