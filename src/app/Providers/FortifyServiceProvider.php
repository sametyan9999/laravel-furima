<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

// Fortify Actions
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;

// Fortify View Response Contracts（明示バインド用）
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /**
         * ====== 重要：View Response を明示バインド ======
         * 環境によって自動解決できないケースを潰します。
         */
        $this->app->singleton(LoginViewResponse::class, function () {
            return new class implements LoginViewResponse {
                public function toResponse($request)
                {
                    return response()->view('auth.login');
                }
            };
        });

        $this->app->singleton(RegisterViewResponse::class, function () {
            return new class implements RegisterViewResponse {
                public function toResponse($request)
                {
                    return response()->view('auth.register');
                }
            };
        });

        $this->app->singleton(RequestPasswordResetLinkViewResponse::class, function () {
            return new class implements RequestPasswordResetLinkViewResponse {
                public function toResponse($request)
                {
                    return response()->view('auth.forgot-password');
                }
            };
        });

        $this->app->singleton(ResetPasswordViewResponse::class, function () {
            return new class implements ResetPasswordViewResponse {
                public function toResponse($request)
                {
                    return response()->view('auth.reset-password', ['request' => $request]);
                }
            };
        });

        $this->app->singleton(VerifyEmailViewResponse::class, function () {
            return new class implements VerifyEmailViewResponse {
                public function toResponse($request)
                {
                    return response()->view('auth.verify-email');
                }
            };
        });

        // 2FAを使うなら（config/fortify.phpで有効な時のみ実際に到達）
        $this->app->singleton(TwoFactorChallengeViewResponse::class, function () {
            return new class implements TwoFactorChallengeViewResponse {
                public function toResponse($request)
                {
                    return response()->view('auth.two-factor-challenge');
                }
            };
        });
    }

    public function boot(): void
    {
        /**
         * ====== Fortify アクション割り当て ======
         */
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        /**
         * ====== Fortify ビュー割り当て（保険） ======
         * こちらも設定しておくと、より堅牢です。
         */
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        // Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
    }
}