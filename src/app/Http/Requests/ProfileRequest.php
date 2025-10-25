<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 画像：.jpeg または .png
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png', 'max:4096'],
            'username'      => ['required', 'string', 'max:20'],
            'postal_code'   => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'address_line1' => ['required', 'string'],
            'address_line2' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.regex' => '郵便番号は「123-4567」の形式で入力してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'avatar'        => 'プロフィール画像',
            'username'      => 'ユーザー名',
            'postal_code'   => '郵便番号',
            'address_line1' => '住所',
            'address_line2' => '建物名',
        ];
    }
}