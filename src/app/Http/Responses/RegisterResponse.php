<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * ユーザー登録成功後のリダイレクト処理
     */
    public function toResponse($request)
    {
        // プロフィール初回設定画面へ遷移
        return redirect()->route('mypage.profile.first');
    }
}