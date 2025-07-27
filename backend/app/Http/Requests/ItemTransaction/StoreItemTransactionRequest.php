<?php

namespace App\Http\Requests\ItemTransaction;

use App\Models\Item;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemTransactionRequest extends FormRequest
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
            'item_id' => ['required', 'uuid', 'exists:items,id'],
            'type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf', 'between:100,500'],
            'metadata' => ['nullable', 'json'],
            'is_verified' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Konfigurasi validasi kustom setelah validasi dasar.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Cek ketersediaan stok hanya jika tipe transaksi adalah 'out'
                if ($this->type === 'out') {
                    $item = Item::find($this->item_id);
                    if ($item && $item->quantity < $this->quantity) {
                        $validator->errors()->add(
                            'quantity',
                            'Stok tidak mencukupi. Sisa stok saat ini: ' . $item->quantity
                        );
                    }
                }
            }
        ];
    }
}
