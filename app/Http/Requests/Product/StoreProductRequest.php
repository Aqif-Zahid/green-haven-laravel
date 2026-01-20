<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'small_description' => ['required', 'string', 'max:500'],
            'description' => ['nullable'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['string'],
            'category' => ['required', 'in:plants,accessories,flowers'],
        ];
    }
}
