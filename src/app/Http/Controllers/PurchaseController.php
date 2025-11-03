<?php

namespace App\Http\Controllers;

use App\Models\{Item, Purchase};
use App\Http\Requests\{PurchaseRequest, AddressRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /** 購入確定（PG06） */
    public function store(PurchaseRequest $request, Item $item)
    {
        $user    = Auth::user();
        $profile = $user->profile;

        if (!$profile || !$profile->postal_code || !$profile->address_line1) {
            return back()->withErrors(['address' => '住所を設定してください'])->withInput();
        }

        try {
            DB::transaction(function () use ($request, $item, $user, $profile) {
                $fresh = Item::whereKey($item->id)->lockForUpdate()->first();
                abort_if(!$fresh || $fresh->status !== 'on_sale', 404, '購入できません');

                $alreadyPurchased = Purchase::where('item_id', $fresh->id)->exists();
                abort_if($alreadyPurchased, 409, '既に購入済みです');

                Purchase::create([
                    'user_id'                  => $user->id,
                    'item_id'                  => $fresh->id,
                    'amount'                   => $fresh->price,
                    'payment_method'           => $request->validated()['payment_method'],
                    'payment_status'           => 'paid',
                    'stripe_payment_intent_id' => null,
                    'purchased_at'             => Carbon::now(),
                    'shipping_name'            => $user->name,
                    'shipping_postal_code'     => $profile->postal_code,
                    'shipping_address1'        => $profile->address_line1,
                    'shipping_address2'        => $profile->address_line2,
                ]);

                $fresh->update([
                    'status'  => 'sold',
                    'sold_at' => Carbon::now(),
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('purchase failed: '.$e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return back()->withErrors(['purchase' => '購入手続きに失敗しました。再度お試しください。'])->withInput();
        }

        return redirect()->route('items.index')->with('status', '購入が完了しました');
    }
}