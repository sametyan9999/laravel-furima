<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 郵便番号：ハイフンあり8文字（例 123-4567）
        return [
            'postal'  => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'address' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'postal.regex' => '郵便番号は「123-4567」の形式で入力してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'postal'  => '郵便番号',
            'address' => '住所',
        ];
    }
}