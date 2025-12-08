<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TradeMessageController;
use App\Http\Controllers\TradeReviewController;

/**
 * 公開ルート
 */
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item}', [ItemController::class, 'show'])
    ->whereNumber('item')
    ->name('items.show');

/**
 * メール認証
 */
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware(['auth'])->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
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
 */
Route::middleware(['auth'])->group(function () {

    Route::get('/mylist', [ItemController::class, 'mylist'])->name('items.mylist');

    Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    Route::post('/items/{item}/like', [ItemController::class, 'like'])
        ->whereNumber('item')->name('items.like');
    Route::delete('/items/{item}/unlike', [ItemController::class, 'unlike'])
        ->whereNumber('item')->name('items.unlike');

    Route::post('/items/{item}/comments', [ItemController::class, 'storeComment'])
        ->whereNumber('item')->name('items.comments.store');

    Route::get('/purchase/{item}', [PurchaseController::class, 'index'])
        ->whereNumber('item')->name('purchase.index');
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])
        ->whereNumber('item')->name('purchase.store');
    Route::get('/purchase/{item}/success', [PurchaseController::class, 'success'])
        ->whereNumber('item')->name('purchase.success');
    Route::get('/purchase/{item}/cancel', [PurchaseController::class, 'cancel'])
        ->whereNumber('item')->name('purchase.cancel');

    Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])
        ->whereNumber('item')->name('purchase.address');
    Route::put('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])
        ->whereNumber('item')->name('purchase.address.update');

    Route::get('/mypage', [ProfileController::class, 'index'])->name('mypage.index');

    Route::middleware('verified')->group(function () {
        Route::get('/mypage/profile', [ProfileController::class, 'edit'])
            ->name('mypage.profile.edit');
        Route::put('/mypage/profile', [ProfileController::class, 'update'])
            ->name('mypage.profile.update');
    });

    /**
     * 取引チャット
     */
    Route::get('/trade/{purchase}', [TradeMessageController::class, 'index'])
        ->name('trade.show');

    Route::post('/trade/{purchase}/message', [TradeMessageController::class, 'store'])
        ->name('trade.store');

    Route::delete('/trade/{purchase}/message/{message}', [TradeMessageController::class, 'destroy'])
        ->name('trade.delete');

    /** 入力保持 API（★追加） */
    Route::post('/trade/{purchase}/draft', [TradeMessageController::class, 'saveDraft'])
        ->name('trade.draft');

    Route::post('/trade/{purchase}/finish', [TradeMessageController::class, 'finish'])
        ->name('trade.finish');

    Route::post('/trade/{purchase}/review', [TradeReviewController::class, 'store'])
        ->name('trade.review.store');
});

/** Stripe Webhook */
Route::post('/stripe/webhook', [PurchaseController::class, 'webhook'])
    ->name('stripe.webhook');