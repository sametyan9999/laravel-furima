<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    /**
     * マイページ（出品した商品 / 購入した商品）
     * ?tab=selling | purchased
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab  = $request->query('tab', 'selling'); // 既定は出品

        // 出品した商品
        $sellingItems = Item::query()
            ->where('user_id', $user->id)
            ->orderByRaw("CASE WHEN status='on_sale' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12, ['*'], 'selling_page');

        // 購入した商品（purchases 経由）
        $purchasedItems = Item::query()
            ->whereHas('purchases', fn ($q) => $q->where('user_id', $user->id))
            ->with('purchases')
            ->orderByRaw("CASE WHEN status='on_sale' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12, ['*'], 'purchased_page');

        return view('mypage.index', compact('user', 'tab', 'sellingItems', 'purchasedItems'));
    }
}