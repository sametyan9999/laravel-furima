<?php

namespace App\Http\Controllers;

use App\Models\{Item, Purchase};
use App\Http\Requests\{PurchaseRequest, AddressRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Stripe SDK
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeCheckout;

class PurchaseController extends Controller
{
    /** 購入画面（PG06） */
    public function index(Request $request, Item $item)
    {
        abort_if($item->status !== 'on_sale', 404);
        abort_if($item->user_id === Auth::id(), 403, '自分の商品は購入できません');

        $profile = Auth::user()->profile;
        $allowed = ['convenience', 'card'];
        $initial = $request->has('payment_method') && in_array($request->input('payment_method'), $allowed, true)
            ? $request->input('payment_method')
            : 'convenience';
        $paymentLabel = $initial === 'card' ? 'カード支払い' : 'コンビニ払い';

        return view('purchase.index', [
            'item'           => $item,
            'profile'        => $profile,
            'initialPayment' => $initial,
            'paymentLabel'   => $paymentLabel,
        ]);
    }

    /** 住所変更画面（PG07） */
    public function editAddress(Item $item)
    {
        $profile = Auth::user()->profile;
        return view('purchase.address', compact('item', 'profile'));
    }

    /** 住所変更保存（PG07） */
    public function updateAddress(AddressRequest $request, Item $item)
    {
        $profile = Auth::user()->profile;
        $profile->fill($request->validated())->save();
        return redirect()->route('purchase.index', $item)->with('status', '住所を更新しました');
    }

    /**
     * 診断用：Stripe Checkout作成前後で詳細なエラーメッセージをUIに表示
     */
    public function store(PurchaseRequest $request, Item $item)
    {
        $user    = Auth::user();
        $profile = $user->profile;

        if (!$profile || !$profile->postal_code || !$profile->address_line1) {
            return back()->withErrors(['address' => '住所を設定してください'])->withInput();
        }
        if ($item->status !== 'on_sale') {
            return back()->withErrors(['purchase' => '販売中ではないため購入できません'])->withInput();
        }

        $method = $request->validated()['payment_method']; // 'convenience' or 'card'

        try {
            // ① .env 読み込み診断
            $secret = config('services.stripe.secret');
            if (empty($secret)) {
                return back()->withErrors([
                    'purchase' => '診断: Stripeシークレットキーが読み込めていません（.envのSTRIPE_SECRETとconfig/services.phpを確認し、php artisan config:clear 実行）。'
                ])->withInput();
            }

            Stripe::setApiKey($secret);

            // 一旦カード固定でテストしてもOK（必要なら下行のコメント解除）
            // $paymentMethodTypes = ['card'];
            $paymentMethodTypes = $method === 'convenience' ? ['konbini'] : ['card'];

            $session = StripeCheckout::create([
                'mode' => 'payment',
                'payment_method_types' => $paymentMethodTypes,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'unit_amount' => $item->price, // 税込・整数
                        'product_data' => ['name' => $item->name],
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => route('purchase.success', $item) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('purchase.cancel',  $item),
                'locale'      => 'ja',
            ]);

            return redirect()->away($session->url);

        } catch (\Throwable $e) {
            Log::error('stripe checkout create failed: '.$e->getMessage(), [
                'file' => $e->getFile(), 'line' => $e->getLine(),
            ]);

            // ② 例外文をそのまま画面に出して特定（後で元に戻します）
            return back()->withErrors([
                'purchase' => 'Stripeエラー: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /** 支払い成功後 */
    public function success(Request $request, Item $item)
    {
        return redirect()->route('items.index')
            ->with('status', '支払い手続きが完了（または実行中）です。反映までお待ちください。');
    }

    /** 支払いキャンセル後 */
    public function cancel(Item $item)
    {
        return redirect()->route('purchase.index', $item)
            ->with('status', '決済をキャンセルしました。');
    }
}