<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;

Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item}', [ItemController::class, 'show'])->whereNumber('item')->name('items.show');

Route::middleware('auth')->group(function () {
    /** マイリスト */
    Route::get('/mylist', [ItemController::class, 'mylist'])->name('items.mylist');

    /** 出品関連 */
    Route::get('/sell',  [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    /** いいね */
    Route::post('/items/{item}/like', [ItemController::class, 'toggleLike'])
        ->whereNumber('item')->name('items.like');

    /** コメント */
    Route::post('/items/{item}/comments', [ItemController::class, 'storeComment'])
        ->whereNumber('item')->name('items.comments.store');

    /** 購入 */
    Route::get('/purchase/{item}', [PurchaseController::class, 'index'])
        ->whereNumber('item')->name('purchase.index');
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])
        ->whereNumber('item')->name('purchase.store');

    /** 住所変更 */
    Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])
        ->whereNumber('item')->name('purchase.address');
    Route::put('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])
        ->whereNumber('item')->name('purchase.address.update');

    /** ✅ マイページ：ProfileController に統一 */
    Route::get('/mypage', [ProfileController::class, 'index'])->name('mypage.index');
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile.edit');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');
});