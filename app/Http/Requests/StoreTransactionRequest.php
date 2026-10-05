<?php

namespace App\Http\Requests;

use App\Models\Product; use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void { $validator->after(function ($validator) { $items = $this->input("items", []); if (!is_array($items)) { return; } $merged = []; foreach ($items as $item) { if (!isset($item["product_id"])) { continue; } $pid = (int) $item["product_id"]; $merged[$pid] = ($merged[$pid] ?? 0) + (int) ($item["qty"] ?? 0); } if (empty($merged)) { return; } $products = Product::whereIn("id", array_keys($merged))->get()->keyBy("id"); foreach ($merged as $pid => $qty) { $product = $products->get($pid); if (!$product) { continue; } if ($qty > $product->stock) { $validator->errors()->add("items", "Stok produk " . $product->name . " tidak mencukupi (stok: " . $product->stock . ", diminta: " . $qty . ")."); } } }); }
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }
}