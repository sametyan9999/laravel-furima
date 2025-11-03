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
        return [
            'postal_code'   => ['required', 'regex:/^\d{3}-\d{4}$/'], // 例 123-4567
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:20'],
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
            'postal_code'   => '郵便番号',
            'address_line1' => '住所',
            'address_line2' => '建物名',
            'phone'         => '電話番号',
        ];
    }
}