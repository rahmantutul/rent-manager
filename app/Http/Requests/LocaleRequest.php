<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canSignIn() ?? false;
    }

    public function rules(): array
    {
        return ['locale' => ['required', Rule::in(array_keys(config('product.locales')))]];
    }
}
