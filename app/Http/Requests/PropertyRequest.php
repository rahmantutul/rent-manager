<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->isMethod('post') ? 'create' : 'update', $this->isMethod('post') ? Property::class : $this->route('property'));
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:150'], 'code' => ['required', 'alpha_dash', 'max:30'], 'type' => ['required', Rule::in(['residential', 'commercial', 'mixed'])], 'address_line1' => ['required', 'string', 'max:255'], 'address_line2' => ['nullable', 'string', 'max:255'], 'city' => ['required', 'string', 'max:100'], 'state' => ['nullable', 'string', 'max:100'], 'postal_code' => ['required', 'string', 'max:30'], 'country_code' => ['required', 'regex:/\A[A-Z]{2}\z/'], 'manager_user_id' => ['nullable', 'integer'], 'notes' => ['nullable', 'string']];
    }
}
