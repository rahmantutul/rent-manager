<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-owner-settings');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'], 'company_address' => ['nullable', 'string', 'max:2000'],
            'currency_code' => ['required', 'regex:/\\A[A-Z]{3}\\z/'],
            'minor_units' => ['required', 'integer', Rule::in([0, 2, 3])],
            'timezone' => ['required', 'timezone', 'max:64'],
            'locale' => ['required', Rule::in(array_keys(config('product.locales')))],
            'date_format' => ['required', Rule::in(config('product.date_formats'))],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ];
    }
}
