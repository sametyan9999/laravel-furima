<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 表の「支払い方法＝選択必須」
        return [
            'payment_method' => ['required', 'in:convenience,card'],
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_method' => '支払い方法',
        ];
    }
}