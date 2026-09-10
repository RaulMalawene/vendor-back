<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('user_id', $userId)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where('user_id', $userId)->where('is_active', true)->whereNull('deleted_at'),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'O cliente seleccionado é inválido.',
            'items.*.product_id.exists' => 'Um dos produtos é inválido ou está inactivo.',
            'items.*.product_id.distinct' => 'Há produtos repetidos na encomenda.',
        ];
    }
}