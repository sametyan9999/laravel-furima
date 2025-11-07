<?php

use Illuminate\Support\Facades\Route;
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
 * メール認証フロー
 */
Route::get('/email/verify', function () {
    return view('auth.verify'); // 誘導画面
})->middleware(['auth'])->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了
    return redirect()->route('mypage.profile.edit');
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->route('items.index');
    }
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:3,1'])->name('verification.send');

/**
 * 会員機能
 * - 既定は auth のみ
 * - プロフィール編集画面だけ verified で保護（テスト要件）
 */
Route::middleware(['auth'])->group(function () {
    /** マイリスト（個別エンドポイントも用意：テスト対策） */
    Route::get('/mylist', [ItemController::class, 'mylist'])->name('items.mylist');
    Route::get('/items/mylist', [ItemController::class, 'mylist'])->name('items.mylist.legacy');

    /** 出品 */
    Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    /** いいね */
    Route::post('/items/{item}/like', [ItemController::class, 'like'])->whereNumber('item')->name('items.like');
    Route::delete('/items/{item}/unlike', [ItemController::class, 'unlike'])->whereNumber('item')->name('items.unlike');

    /** コメント */
    Route::post('/items/{item}/comments', [ItemController::class, 'storeComment'])
        ->whereNumber('item')->name('items.comments.store');

    /** 購入 */
    Route::get('/purchase/{item}', [PurchaseController::class, 'index'])->whereNumber('item')->name('purchase.index');
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])->whereNumber('item')->name('purchase.store');
    Route::get('/purchase/{item}/success', [PurchaseController::class, 'success'])->whereNumber('item')->name('purchase.success');
    Route::get('/purchase/{item}/cancel', [PurchaseController::class, 'cancel'])->whereNumber('item')->name('purchase.cancel');

    /** 住所変更 */
    Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])
        ->whereNumber('item')->name('purchase.address');
    Route::put('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])
        ->whereNumber('item')->name('purchase.address.update');

    /** マイページ */
    Route::get('/mypage', [ProfileController::class, 'index'])->name('mypage.index');

    /** プロフィール編集だけ verified で保護（ここが重要） */
    Route::middleware('verified')->group(function () {
        Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile.edit');
        Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');
    });
});

/**
 * Stripe Webhook（認証不要）
 * - コンビニ支払いの入金完了などを通知する
 */
Route::post('/stripe/webhook', [PurchaseController::class, 'webhook'])->name('stripe.webhook');