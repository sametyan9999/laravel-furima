<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TradeMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 本文：必須、最大400文字
            'body'  => ['required', 'string', 'max:400'],

            // 画像：任意、jpeg or png
            'image' => ['nullable', 'image', 'mimes:jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            // 本文
            'body.required' => '本文を入力してください',
            'body.max'      => '本文は400文字以内で入力してください',

            // 画像（jpeg / png 以外）
            'image.image'   => '「.png」または「.jpeg」形式でアップロードしてください',
            'image.mimes'   => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}