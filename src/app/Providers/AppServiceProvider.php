<?php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use App\Http\Responses\RegisterResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fortify の登録後レスポンスを上書き（登録時のみメール認証画面に誘導）
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}