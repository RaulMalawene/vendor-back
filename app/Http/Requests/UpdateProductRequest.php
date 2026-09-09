<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:191'],
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:191',
                Rule::unique('products', 'sku')
                    ->where('user_id', $userId)
                    ->ignore($this->route('product')),
            ],
            'category_id' => [
                'sometimes',
                'nullable',
                Rule::exists('categories', 'id')->where('user_id', $userId),
            ],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'Já tem um produto com este SKU.',
        ];
    }
}