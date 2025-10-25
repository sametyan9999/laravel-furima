<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * 新規ユーザー登録時のバリデーションと登録処理
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            // 名前が未入力のときエラー
            'name' => ['required', 'string', 'max:255'],

            // メールが未入力・形式不正・重複のときエラー
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],

            // パスワードが未入力・7文字以下・確認用と不一致のときエラー
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        // 全項目が正しい場合のみ登録処理
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}