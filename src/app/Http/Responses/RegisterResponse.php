<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * ユーザー登録成功後のリダイレクト
     * 仕様：プロフィール設定画面へ遷移
     */
    public function toResponse($request)
    {
        return redirect()->route('mypage.profile.edit');
    }
}