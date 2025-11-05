<?php
declare(strict_types=1);

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): Response
    {
        // 登録直後はプロフィール設定画面へ
        //   （ただし mypage.profile.edit は verified で保護されているため
        //    未認証ユーザーは /email/verify へ自動リダイレクトされる）
        return redirect()->route('mypage.profile.edit');
    }
}