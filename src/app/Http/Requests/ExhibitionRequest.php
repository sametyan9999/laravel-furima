<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string'],
            'description'  => ['required', 'string', 'max:255'],
            // 画像は「アップロード必須・jpeg|png」
            'image_file'   => ['required', 'image', 'mimes:jpeg,png', 'max:4096'],
            'category_id'  => ['required', 'exists:categories,id'],
            'condition_id' => ['required', 'integer', 'exists:conditions,id'],
            'price'        => ['required', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'         => '商品名',
            'description'  => '商品説明',
            'image_file'   => '商品画像',
            'category_id'  => '商品のカテゴリー',
            'condition_id' => '商品の状態',
            'price'        => '商品価格',
        ];
    }
}