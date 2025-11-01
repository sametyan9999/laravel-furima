<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 誰でも登録できる想定
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email:filter', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => '名前は必須です。',
            'email.required'                 => 'メールアドレスは必須です。',
            'email.email'                    => 'メールアドレスの形式が正しくありません。',
            'email.unique'                   => 'このメールアドレスは既に使用されています。',
            'password.required'              => 'パスワードは必須です。',
            'password.min'                   => 'パスワードは8文字以上で入力してください。',
            'password.confirmed'             => '確認用パスワードが一致しません。',
            'password_confirmation.required' => '確認用パスワードは必須です。',
            'password_confirmation.min'      => '確認用パスワードは8文字以上で入力してください。',
        ];
    }
}
