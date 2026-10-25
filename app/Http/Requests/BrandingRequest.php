<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-global-branding');
    }

    public function rules(): array
    {
        return [
            'product_name' => ['required', 'string', 'max:150'],
            'primary_color' => ['required', 'regex:/\\A#[0-9a-fA-F]{6}\\z/'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ];
    }
}
