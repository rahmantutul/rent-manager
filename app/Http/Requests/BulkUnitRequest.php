<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', $this->route('building'));
    }

    public function rules(): array
    {
        return ['from_floor' => ['required', 'integer', 'between:-100,500'], 'to_floor' => ['required', 'integer', 'gte:from_floor', 'between:-100,500'], 'units_per_floor' => ['required', 'integer', 'between:1,100'], 'pattern' => ['required', Rule::in(['floor_letter', 'floor_number'])], 'unit_type' => ['required', Rule::in(['apartment', 'shop', 'office', 'parking', 'storage', 'other'])], 'default_rent' => ['required', 'integer', 'min:0']];
    }
}
