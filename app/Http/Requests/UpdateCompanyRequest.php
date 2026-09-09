<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'contact_name' => ['nullable', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:191'],
            'bank_name' => ['nullable', 'string', 'max:191'],
            'bank_account_holder' => ['nullable', 'string', 'max:191'],
            'bank_account_number' => ['nullable', 'string', 'max:34'],
        ];
    }
}