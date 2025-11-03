<?php

use Illuminate\Support\Facades\Route;
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
 * マイリスト
 */
Route::get('/mylist', [ItemController::class, 'mylist'])
    ->middleware('auth')
    ->name('items.mylist');
Route::get('/items/mylist', [ItemController::class, 'mylist'])
    ->middleware('auth')
    ->name('items.mylist.legacy');

/**
 * 出品
 */
Route::get('/sell', [ItemController::class, 'create'])
    ->middleware('auth')
    ->name('items.create');
Route::post('/sell', [ItemController::class, 'store'])
    ->middleware('auth')
    ->name('items.store');

/**
 * いいね
 * - 追加:  POST   /items/{item}/like   => items.like
 * - 解除:  DELETE /items/{item}/unlike => items.unlike
 *   （テストは DELETE /items/{item}/unlike を叩く構成）
 */
Route::post('/items/{item}/like', [ItemController::class, 'like'])
    ->whereNumber('item')
    ->middleware('auth')
    ->name('items.like');

Route::delete('/items/{item}/unlike', [ItemController::class, 'unlike'])
    ->whereNumber('item')
    ->middleware('auth')
    ->name('items.unlike');

/**
 * コメント
 */
Route::post('/items/{item}/comments', [ItemController::class, 'storeComment'])
    ->whereNumber('item')
    ->middleware('auth')
    ->name('items.comments.store');

/**
 * 購入
 */
Route::get('/purchase/{item}', [PurchaseController::class, 'index'])
    ->whereNumber('item')
    ->middleware('auth')
    ->name('purchase.index');
Route::post('/purchase/{item}', [PurchaseController::class, 'store'])
    ->whereNumber('item')
    ->middleware('auth')
    ->name('purchase.store');

/**
 * 住所変更
 */
Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])
    ->whereNumber('item')
    ->middleware('auth')
    ->name('purchase.address');
Route::put('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])
    ->whereNumber('item')
    ->middleware('auth')
    ->name('purchase.address.update');

/**
 * マイページ
 */
Route::get('/mypage', [ProfileController::class, 'index'])
    ->middleware('auth')
    ->name('mypage.index');
Route::get('/mypage/profile', [ProfileController::class, 'edit'])
    ->middleware('auth')
    ->name('mypage.profile.edit');
Route::put('/mypage/profile', [ProfileController::class, 'update'])
    ->middleware('auth')
    ->name('mypage.profile.update');