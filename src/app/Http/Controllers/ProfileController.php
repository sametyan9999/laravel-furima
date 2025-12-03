<?php

namespace App\Http\Controllers;

use App\Models\{Item, Purchase, Profile};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProfileRequest; // ← 追加

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $view = $this->normalizeViewParam($request);

        $profile = $user->profile;
        $bought = null;
        $sold = null;
        $trading = null;
        $trade_unread_total = 0;

        if ($view === 'buy') {

            $bought = $this->getBoughtItems($user, $request);

        } elseif ($view === 'trade') {

            $trading = $this->getTradingItems($user, $request);
            $trade_unread_total = $trading->sum('unread');

        } else {

            $sold = $this->getSoldItems($user, $request);
        }

        return view('mypage.index', compact(
            'user',
            'profile',
            'bought',
            'sold',
            'trading',
            'trade_unread_total',
            'view'
        ));
    }

    private function normalizeViewParam(Request $request): string
    {
        if ($request->has('page')) {
            $legacy = (string) $request->query('page');
            if (in_array($legacy, ['buy', 'sell', 'trade'], true)) {
                $request->merge(['view' => $legacy]);
            }
        }

        return (string) $request->query('view', 'sell');
    }

    private function getBoughtItems($user, Request $request)
    {
        return Purchase::with('item')
            ->where('user_id', $user->id)
            ->latest('purchased_at')
            ->paginate(12, ['*'], 'buy_page')
            ->appends($request->except('buy_page'));
    }

    private function getSoldItems($user, Request $request)
    {
        return Item::where('user_id', $user->id)
            ->latest()
            ->paginate(12, ['*'], 'sell_page')
            ->appends($request->except('sell_page'));
    }

    /**
     * ▼ 追加：取引中の商品一覧
     */
    private function getTradingItems($user, Request $request)
    {
        return DB::table('purchases')
            ->join('items', 'purchases.item_id', '=', 'items.id')
            ->where(function ($q) use ($user) {
                $q->where('purchases.user_id', $user->id)
                  ->orWhere('items.user_id', $user->id);
            })
            ->select(
                'purchases.id as purchase_id',
                'items.name',
                'items.image',

                DB::raw('(SELECT COUNT(*) FROM trade_messages
                          WHERE trade_messages.purchase_id = purchases.id
                          AND trade_messages.user_id != '.$user->id.'
                          AND trade_messages.is_deleted = 0
                         ) AS unread')
            )
            ->latest('purchases.created_at')
            ->paginate(12, ['*'], 'trade_page')
            ->appends($request->except('trade_page'));
    }

    public function edit()
    {
        $profile = Auth::user()->profile;
        return view('mypage.profile', compact('profile'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        $user->name = $data['username'];
        $user->save();

        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $profile->postal_code = $data['postal_code'];
        $profile->address_line1 = $data['address_line1'];
        $profile->address_line2 = $data['address_line2'] ?? null;

        if (isset($data['phone'])) $profile->phone = $data['phone'];
        if (isset($data['bio'])) $profile->bio = $data['bio'];

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar_path = '/storage/' . $path;
        }

        $profile->save();

        return redirect()->route('mypage.index')
            ->with('status', 'プロフィールを更新しました');
    }

    public function first()
    {
        $profile = Auth::user()->profile;
        return view('mypage.profile-first', compact('profile'));
    }

    public function storeFirst(Request $request)
    {
        $data = $request->validate([
            'postal_code' => ['required', 'string', 'size:8', 'regex:/^\d{3}-\d{4}$/'],
            'address_line1' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $profile->fill($data)->save();

        return redirect()->route('mypage.index');
    }
}