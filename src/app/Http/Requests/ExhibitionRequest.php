<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'brand'        => ['nullable', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'price'        => ['required', 'integer', 'min:1'],
            'condition_id' => ['required', 'exists:conditions,id'],
            // ここがポイント：単一ではなく配列として必須
            'category_ids'   => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'image_file'     => ['required', 'image', 'mimes:jpg,jpeg,png,gif', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_ids'   => 'カテゴリ',
            'category_ids.*' => 'カテゴリ',
            'image_file'     => '商品画像',
        ];
    }
}