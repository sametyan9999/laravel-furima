<?php

namespace App\Http\Controllers;

use App\Models\{Item, Purchase};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class PurchaseController extends Controller
{
    /**
     * 購入画面（PG06）
     */
    public function index(Item $item)
    {
        abort_if($item->status !== 'on_sale', 404);
        abort_if($item->user_id === Auth::id(), 403, '自分の商品は購入できません');

        $profile = Auth::user()->profile; // 住所の初期値
        return view('purchase.index', compact('item', 'profile'));
    }

    /**
     * 住所変更画面（PG07）
     */
    public function editAddress(Item $item)
    {
        $profile = Auth::user()->profile;
        return view('purchase.address', compact('item', 'profile'));
    }

    /**
     * 住所変更保存
     * AddressRequestに一致：postal_code(123-4567)、address_line1必須、address_line2任意
     */
    public function updateAddress(Request $request, Item $item)
    {
        $data = $request->validate([
            'postal_code'   => ['required','string','size:8','regex:/^\d{3}-\d{4}$/'],
            'address_line1' => ['required','string','max:255'],
            'address_line2' => ['nullable','string','max:255'],
            'phone'         => ['nullable','string','max:20'],
        ]);

        $profile = Auth::user()->profile;
        $profile->fill($data)->save();

        return redirect()->route('purchase.index', $item)->with('status', '住所を更新しました');
    }

    /**
     * 購入確定
     * 支払い方法は「card / convenience」（US006-FN022/FN023）
     */
    public function store(Request $request, Item $item)
    {
        abort_if($item->status !== 'on_sale', 404);

        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['card','convenience'])],
        ]);

        $profile = Auth::user()->profile;
        if (!$profile || !$profile->postal_code || !$profile->address_line1) {
            return back()->withErrors(['address' => '住所を設定してください']);
        }

        // 簡易：決済は成功扱い
        $purchase = Purchase::create([
            'buyer_user_id'        => Auth::id(),
            'item_id'              => $item->id,
            'amount'               => $item->price,
            'payment_method'       => $data['payment_method'],
            'payment_status'       => 'paid',
            'stripe_payment_intent_id' => null,
            'purchased_at'         => Carbon::now(),
            // スナップショット
            'shipping_name'        => Auth::user()->name,
            'shipping_postal_code' => $profile->postal_code,
            'shipping_address1'    => $profile->address_line1,
            'shipping_address2'    => $profile->address_line2,
        ]);

        // 商品をSoldに
        $item->update([
            'status'  => 'sold',
            'sold_at' => Carbon::now(),
        ]);

        return redirect()->route('items.show', $item)->with('status', '購入が完了しました');
    }
}