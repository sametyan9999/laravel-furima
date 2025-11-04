<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;

/**
 * 公開ルート
 */
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item}', [ItemController::class, 'show'])
    ->whereNumber('item')
    ->name('items.show');

/**
 * メール認証フロー（Fortify標準）
 */
Route::get('/email/verify', function () {
    return view('auth.verify'); // 誘導画面
})->middleware(['auth'])->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了
    return redirect()->route('mypage.profile.edit'); // プロフィール編集へ
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->route('items.index');
    }
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:3,1'])->name('verification.send');

/**
 * （テスト手順②対応）local限定：ボタン押下で認証サイトへ直遷移
 */
Route::get('/email/verify/direct', function () {
    abort_unless(app()->environment('local') && auth()->check(), 403);
    $signed = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => auth()->id(), 'hash' => sha1(auth()->user()->email)]
    );
    return redirect($signed);
})->middleware(['auth'])->name('verification.direct');

/**
 * 会員向け機能（要ログイン＋メール認証）
 */
Route::middleware(['auth', 'verified'])->group(function () {

    // マイリスト
    Route::get('/mylist', [ItemController::class, 'mylist'])->name('items.mylist');
    Route::get('/items/mylist', [ItemController::class, 'mylist'])->name('items.mylist.legacy');

    // 出品
    Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    // いいね
    Route::post('/items/{item}/like', [ItemController::class, 'like'])->whereNumber('item')->name('items.like');
    Route::delete('/items/{item}/unlike', [ItemController::class, 'unlike'])->whereNumber('item')->name('items.unlike');

    // コメント
    Route::post('/items/{item}/comments', [ItemController::class, 'storeComment'])
        ->whereNumber('item')->name('items.comments.store');

    // 購入
    Route::get('/purchase/{item}', [PurchaseController::class, 'index'])->whereNumber('item')->name('purchase.index');
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])->whereNumber('item')->name('purchase.store');
    Route::get('/purchase/{item}/success', [PurchaseController::class, 'success'])->whereNumber('item')->name('purchase.success');
    Route::get('/purchase/{item}/cancel', [PurchaseController::class, 'cancel'])->whereNumber('item')->name('purchase.cancel');

    // マイページ
    Route::get('/mypage', [ProfileController::class, 'index'])->name('mypage.index');
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile.edit');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');
});