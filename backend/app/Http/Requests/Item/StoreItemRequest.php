<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'specs' => ['nullable', 'json'],
            'quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'between:100,500'], 
        ];
    }
}
