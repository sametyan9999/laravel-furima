<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;

/**
 * ==========================
 * Public
 * ==========================
 */

// トップ（商品一覧：おすすめ／マイリストは ?tab=mylist で切替）PG01/PG02
Route::get('/', [ItemController::class, 'index'])->name('items.index');

// 商品詳細 PG05
Route::get('/item/{item}', [ItemController::class, 'show'])
    ->whereNumber('item')
    ->name('items.show');

/**
 * ==========================
 * Authenticated (要ログイン)
 * ==========================
 */

// Fortify のメール認証ルート（/email/verify）にアクセスした場合 → トップページへリダイレクト
Route::get('/email/verify', function () {
    return redirect()->route('items.index');
})->name('verification.notice');

// Fortify のログイン後のリダイレクト先をトップページに統一
if (defined('App\\Providers\\RouteServiceProvider::HOME') === false) {
    define('App\\Providers\\RouteServiceProvider::HOME', '/');
}

Route::middleware(['auth'])->group(function () {

    /** ⑤ マイリスト（いいね一覧） */
    Route::get('/mylist', [ItemController::class, 'mylist'])->name('items.mylist');

    /** 出品関連 */
    Route::get('/sell',  [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    /** いいね機能 */
    Route::post('/items/{item}/like', [ItemController::class, 'toggleLike'])
        ->whereNumber('item')
        ->name('items.like');

    /** コメント投稿 */
    Route::post('/items/{item}/comments', [ItemController::class, 'storeComment'])
        ->whereNumber('item')
        ->name('items.comments.store');

    /** 購入フロー（最小構成） */
    Route::get('/purchase/{item}',  [PurchaseController::class, 'index'])
        ->whereNumber('item')
        ->name('purchase.index');
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])
        ->whereNumber('item')
        ->name('purchase.store');

    /** 住所変更（互換ルート含む） */
    Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])
        ->whereNumber('item')
        ->name('purchase.address');
    Route::get('/purchase/address/{item}/edit', [PurchaseController::class, 'editAddress'])
        ->whereNumber('item')
        ->name('purchase.address.edit');
    Route::put('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])
        ->whereNumber('item')
        ->name('purchase.address.update');

    /** マイページ PG09〜PG12 */
    Route::get('/mypage', [ProfileController::class, 'index'])->name('mypage.index');
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile.edit');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');

    /** 初回プロフィール設定（任意） */
    Route::get('/mypage/profile-first',  [ProfileController::class, 'first'])->name('mypage.profile.first');
    Route::post('/mypage/profile-first', [ProfileController::class, 'storeFirst'])->name('mypage.profile.first.store');
});
