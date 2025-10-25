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

// トップ（商品一覧：おすすめ／マイリストは ?tab=mylist で切替） PG01/PG02
Route::get('/', [ItemController::class, 'index'])->name('items.index');

// 商品詳細 PG05
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');


/**
 * ==========================
 * Authenticated (要ログイン)
 * ==========================
 */
Route::middleware(['auth'])->group(function () {

    // 出品 PG08
    Route::get('/sell',  [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    // いいね（US005-FN018）
    Route::post('/items/{item}/like', [ItemController::class, 'toggleLike'])->name('items.like');

    // コメント送信（US006-FN020）
    Route::post('/items/{item}/comments', [ItemController::class, 'storeComment'])->name('items.comments.store');

    // 購入フロー PG06
    Route::get('/purchase/{item}',  [PurchaseController::class, 'index'])->name('purchase.index');
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])->name('purchase.store');

    // 送付先住所変更 PG07
    Route::get ('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])->name('purchase.address');
    Route::post('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    // マイページ関連 PG09〜PG12
    Route::get('/mypage', [ProfileController::class, 'index'])->name('mypage.index');
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile.edit');
    Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');

    // 初回プロフィール設定（任意）
    Route::get ('/mypage/profile-first',  [ProfileController::class, 'first'])->name('mypage.profile.first');
    Route::post('/mypage/profile-first',  [ProfileController::class, 'storeFirst'])->name('mypage.profile.first.store');
});