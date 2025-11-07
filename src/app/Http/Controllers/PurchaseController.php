<?php

namespace App\Http\Controllers;

use App\Models\{Item, Purchase};
use App\Http\Requests\{PurchaseRequest, AddressRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
     * 購入処理
     * - testing：即時購入成立
     * - それ以外：Stripe Checkout
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

        $method = $request->validated()['payment_method']; // 'convenience' | 'card'

        // ✅ テスト環境は即確定
        if (app()->environment('testing')) {
            DB::transaction(function () use ($user, $item, $profile) {
                Purchase::create([
                    'user_id'              => $user->id,
                    'item_id'              => $item->id,
                    'shipping_postal_code' => $profile->postal_code,
                    'shipping_address1'    => $profile->address_line1,
                    'shipping_address2'    => $profile->address_line2,
                ]);
                $item->update(['status' => 'sold', 'sold_at' => now()]);
            });

            return redirect()->route('items.index')->with('status', '購入が完了しました。');
        }

        // --- 本番/開発は Stripe へ ---
        try {
            $secret = config('services.stripe.secret');
            if (empty($secret)) {
                return back()->withErrors([
                    'purchase' => '診断: Stripeシークレットキーが読み込めていません（.env と config/services.php を確認し、php artisan config:clear）。'
                ])->withInput();
            }

            Stripe::setApiKey($secret);
            $paymentMethodTypes = $method === 'convenience' ? ['konbini'] : ['card'];

            $session = StripeCheckout::create([
                'mode' => 'payment',
                'payment_method_types' => $paymentMethodTypes,
                'line_items' => [[
                    'price_data' => [
                        'currency'    => 'jpy',
                        'unit_amount' => $item->price,
                        'product_data'=> ['name' => $item->name],
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => route('purchase.success', $item) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('purchase.cancel',  $item),
                'locale'      => 'ja',
                // Webhook側で特定するためのメタデータ（両方に付与）
                'metadata' => [
                    'item_id' => (string) $item->id,
                    'user_id' => (string) $user->id,
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'item_id' => (string) $item->id,
                        'user_id' => (string) $user->id,
                    ],
                ],
            ]);

            return redirect()->away($session->url);

        } catch (\Throwable $e) {
            Log::error('stripe checkout create failed: '.$e->getMessage(), [
                'file' => $e->getFile(), 'line' => $e->getLine(),
            ]);

            return back()->withErrors([
                'purchase' => 'Stripeエラー: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * 支払い成功後
     * - カード払い：ここで確定（paid のみ）
     * - コンビニ：未入金のため確定しない
     */
    public function success(Request $request, Item $item)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('items.index')
                ->with('status', '支払い手続きが完了（または実行中）です。反映までお待ちください。');
        }

        try {
            $secret = config('services.stripe.secret');
            if (!$secret) {
                return redirect()->route('items.index')
                    ->with('status', '決済結果の反映に失敗しました（Stripeキー未設定）。');
            }
            \Stripe\Stripe::setApiKey($secret);

            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if (($session->payment_status ?? null) === 'paid') {
                DB::transaction(function () use ($item) {
                    $fresh = Item::lockForUpdate()->find($item->id);
                    if (($fresh->status ?? null) === 'sold' || $fresh->purchases()->exists()) {
                        return;
                    }

                    $user    = Auth::user();
                    $profile = $user->profile;

                    Purchase::create([
                        'user_id'              => $user->id,
                        'item_id'              => $fresh->id,
                        'shipping_postal_code' => $profile->postal_code,
                        'shipping_address1'    => $profile->address_line1,
                        'shipping_address2'    => $profile->address_line2,
                    ]);

                    $fresh->update(['status' => 'sold', 'sold_at' => now()]);
                });

                return redirect()->route('items.index')->with('status', '購入が完了しました。');
            }

            return redirect()->route('items.index')
                ->with('status', '支払い手続きが完了（または実行中）です。反映までお待ちください。');

        } catch (\Throwable $e) {
            Log::error('stripe success finalize failed: '.$e->getMessage(), [
                'file' => $e->getFile(), 'line' => $e->getLine(),
            ]);

            return redirect()->route('items.index')
                ->with('status', '決済結果の反映に失敗しました。時間を置いて再表示してください。');
        }
    }

    /**
     * Stripe Webhook（コンビニ払いの入金確定／カードの保険）
     * - 署名検証（STRIPE_WEBHOOK_SECRET 必須）
     * - 対象イベント: payment_intent.succeeded / checkout.session.completed
     */
    public function webhook(Request $request)
    {
        $endpointSecret = config('services.stripe.webhook_secret');
        if (!$endpointSecret) {
            Log::warning('stripe webhook_secret not set');
            return response('no secret', 400);
        }

        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, $endpointSecret);
        } catch (\Throwable $e) {
            Log::warning('stripe webhook verify failed: '.$e->getMessage());
            return response('invalid signature', 400);
        }

        $type   = $event->type;
        $object = $event->data->object;

        // 1) 入金済み（コンビニはこれが来る）
        if ($type === 'payment_intent.succeeded') {
            $itemId = $object->metadata->item_id ?? null;
            $userId = $object->metadata->user_id ?? null;
            $this->finalizePurchaseByIds($itemId, $userId);
        }

        // 2) セッション完了（カードはこの時点で paid ことが多い）
        if ($type === 'checkout.session.completed') {
            // paidでなければスキップ（コンビニは unpaid）
            if (($object->payment_status ?? null) === 'paid') {
                $itemId = $object->metadata->item_id ?? null;
                $userId = $object->metadata->user_id ?? null;
                $this->finalizePurchaseByIds($itemId, $userId);
            }
        }

        return response('ok', 200);
    }

    /** 共同ロジック：購入作成＋sold化（冪等） */
    private function finalizePurchaseByIds(?string $itemId, ?string $userId): void
    {
        if (!$itemId || !$userId) {
            Log::info('finalize skipped: missing metadata', ['item_id' => $itemId, 'user_id' => $userId]);
            return;
        }

        DB::transaction(function () use ($itemId, $userId) {
            /** @var Item|null $item */
            $item = Item::lockForUpdate()->find($itemId);
            if (!$item) return;

            if (($item->status ?? null) === 'sold' || $item->purchases()->exists()) {
                return; // 冪等
            }

            // 購入者のプロフィール（無くても空で作る）
            $user = \App\Models\User::find($userId);
            $postal  = $user?->profile?->postal_code ?? '';
            $addr1   = $user?->profile?->address_line1 ?? '';
            $addr2   = $user?->profile?->address_line2 ?? '';

            Purchase::create([
                'user_id'              => $userId,
                'item_id'              => $item->id,
                'shipping_postal_code' => $postal,
                'shipping_address1'    => $addr1,
                'shipping_address2'    => $addr2,
            ]);

            $item->update(['status' => 'sold', 'sold_at' => now()]);
        });
    }

    /** 支払いキャンセル後 */
    public function cancel(Item $item)
    {
        return redirect()->route('purchase.index', $item)
            ->with('status', '決済をキャンセルしました。');
    }
}