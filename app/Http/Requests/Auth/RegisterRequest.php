<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['required', 'string', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Já existe uma conta com este email.',
            'password.confirmed' => 'A confirmação da palavra-passe não coincide.',
        ];
    }
}