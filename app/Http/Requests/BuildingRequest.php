<?php

namespace App\Http\Requests;

use App\Models\Building;
use Illuminate\Foundation\Http\FormRequest;

class BuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->isMethod('post') ? 'create' : 'update', $this->isMethod('post') ? Building::class : $this->route('building'));
    }

    public function rules(): array
    {
        return ['property_id' => ['required', 'integer'], 'name' => ['required', 'string', 'max:100'], 'floors_count' => ['nullable', 'integer', 'between:0,500']];
    }
}
