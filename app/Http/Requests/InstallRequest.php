<?php

namespace App\Http\Requests;

use App\Services\Installation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! app(Installation::class)->installed();
    }

    public function rules(): array
    {
        return [
            'app_url' => ['required', 'url:http,https', 'max:255'],
            'db_host' => ['required', 'regex:/\\A[a-zA-Z0-9.:-]+\\z/', 'max:255'],
            'db_port' => ['required', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'regex:/\\A[a-zA-Z0-9_-]+\\z/', 'max:64'],
            'db_username' => ['required', 'string', 'max:100', 'not_regex:/[\\r\\n]/'],
            'db_password' => ['nullable', 'string', 'max:255', 'not_regex:/[\\r\\n]/'],
            'company_name' => ['required', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'max:255', 'confirmed'],
            'currency_code' => ['required', 'regex:/\\A[A-Z]{3}\\z/'],
            'minor_units' => ['required', 'integer', Rule::in([0, 2, 3])],
            'timezone' => ['required', 'timezone', 'max:64'],
            'locale' => ['required', Rule::in(array_keys(config('product.locales')))],
            'mail_mailer' => ['required', Rule::in(['log', 'smtp'])],
            'mail_host' => ['nullable', 'required_if:mail_mailer,smtp', 'string', 'max:255', 'not_regex:/[\\r\\n]/'],
            'mail_port' => ['nullable', 'required_if:mail_mailer,smtp', 'integer', 'between:1,65535'],
            'mail_username' => ['nullable', 'string', 'max:255', 'not_regex:/[\\r\\n]/'],
            'mail_password' => ['nullable', 'string', 'max:255', 'not_regex:/[\\r\\n]/'],
            'mail_scheme' => ['nullable', Rule::in(['smtp', 'smtps'])],
        ];
    }
}
