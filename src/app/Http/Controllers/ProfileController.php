<?php

namespace App\Http\Controllers;

use App\Models\{Item, Purchase, Profile};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $view = $this->normalizeViewParam($request);

        $profile = $user->profile;
        $bought  = null;
        $sold    = null;
        $trading = null;

        // ▼ 常に表示する：全取引の未返信メッセージ合計
        $trade_unread_total_all = $this->getAllTradeUnreadTotal($user);

        // ▼ 取引中タブで使う合計
        $trade_unread_total = 0;

        if ($view === 'buy') {

            $bought = $this->getBoughtItems($user, $request);

        } elseif ($view === 'trade') {

            $trading = $this->getTradingItems($user, $request);
            $trade_unread_total = (int) $trading->getCollection()->sum('unread');

        } else {

            $sold = $this->getSoldItems($user, $request);
        }

        // 評価平均
        $reviewAvg = $user->getReviewAverage();

        return view('mypage.index', compact(
            'user',
            'profile',
            'bought',
            'sold',
            'trading',
            'trade_unread_total',
            'trade_unread_total_all',
            'reviewAvg',
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
     * 全取引の未返信メッセージ合計
     * 対象: 自分がまだレビューしていない取引のみ
     */
    private function getAllTradeUnreadTotal($user): int
    {
        $userId = (int) $user->id;

        // 自分の最後のメッセージ
        $myLastMessageSub = DB::table('trade_messages as tm_me')
            ->select(
                'tm_me.purchase_id',
                DB::raw('MAX(tm_me.created_at) as my_last_at')
            )
            ->where('tm_me.user_id', $userId)
            ->where('tm_me.is_deleted', 0)
            ->groupBy('tm_me.purchase_id');

        // 相手の未返信メッセージ
        $unreadSub = DB::table('trade_messages as tm_other')
            ->leftJoinSub($myLastMessageSub, 'my_last', function ($join) {
                $join->on('tm_other.purchase_id', '=', 'my_last.purchase_id');
            })
            ->select(
                'tm_other.purchase_id',
                DB::raw('COUNT(*) as unread')
            )
            ->where('tm_other.is_deleted', 0)
            ->where('tm_other.user_id', '!=', $userId)
            ->where(function ($q) {
                $q->whereColumn('tm_other.created_at', '>', 'my_last.my_last_at')
                  ->orWhereNull('my_last.my_last_at');
            })
            ->groupBy('tm_other.purchase_id');

        // 自分のレビュー有無
        $myReviewSub = DB::table('trade_reviews')
            ->select(
                'purchase_id',
                DB::raw('COUNT(*) as my_review_count')
            )
            ->where('reviewer_id', $userId)
            ->groupBy('purchase_id');

        // ★ 自分がレビューしていない取引のみを対象にする
        return (int) DB::table('purchases')
            ->join('items', 'purchases.item_id', '=', 'items.id')
            ->leftJoinSub($unreadSub, 'unreads', function ($join) {
                $join->on('purchases.id', '=', 'unreads.purchase_id');
            })
            ->leftJoinSub($myReviewSub, 'my_review', function ($join) {
                $join->on('purchases.id', '=', 'my_review.purchase_id');
            })
            ->where(function ($q) use ($userId) {
                $q->where('purchases.user_id', $userId)
                  ->orWhere('items.user_id', $userId);
            })
            ->whereNull('my_review.my_review_count')  // ← 修正ポイント
            ->sum(DB::raw('COALESCE(unreads.unread, 0)'));
    }

    /**
     * 取引中商品の一覧取得
     * 条件: 自分がまだレビューしていない取引のみ表示
     */
    private function getTradingItems($user, Request $request)
    {
        $userId = (int) $user->id;

        $myLastMessageSub = DB::table('trade_messages as tm_me')
            ->select(
                'tm_me.purchase_id',
                DB::raw('MAX(tm_me.created_at) as my_last_at')
            )
            ->where('tm_me.user_id', $userId)
            ->where('tm_me.is_deleted', 0)
            ->groupBy('tm_me.purchase_id');

        $unreadSub = DB::table('trade_messages as tm_other')
            ->leftJoinSub($myLastMessageSub, 'my_last', function ($join) {
                $join->on('tm_other.purchase_id', '=', 'my_last.purchase_id');
            })
            ->select(
                'tm_other.purchase_id',
                DB::raw('COUNT(*) as unread')
            )
            ->where('tm_other.is_deleted', 0)
            ->where('tm_other.user_id', '!=', $userId)
            ->where(function ($q) {
                $q->whereColumn('tm_other.created_at', '>', 'my_last.my_last_at')
                  ->orWhereNull('my_last.my_last_at');
            })
            ->groupBy('tm_other.purchase_id');

        $lastMessageSub = DB::table('trade_messages as tm_all')
            ->select(
                'tm_all.purchase_id',
                DB::raw('MAX(tm_all.created_at) as last_message_at')
            )
            ->where('tm_all.is_deleted', 0)
            ->groupBy('tm_all.purchase_id');

        // 自分のレビュー有無
        $myReviewSub = DB::table('trade_reviews')
            ->select(
                'purchase_id',
                DB::raw('COUNT(*) as my_review_count')
            )
            ->where('reviewer_id', $userId)
            ->groupBy('purchase_id');

        // ★ 自分がレビューしていない取引のみを一覧表示
        return DB::table('purchases')
            ->join('items', 'purchases.item_id', '=', 'items.id')
            ->leftJoinSub($unreadSub, 'unreads', function ($join) {
                $join->on('purchases.id', '=', 'unreads.purchase_id');
            })
            ->leftJoinSub($lastMessageSub, 'last_msg', function ($join) {
                $join->on('purchases.id', '=', 'last_msg.purchase_id');
            })
            ->leftJoinSub($myReviewSub, 'my_review', function ($join) {
                $join->on('purchases.id', '=', 'my_review.purchase_id');
            })
            ->where(function ($q) use ($userId) {
                $q->where('purchases.user_id', $userId)
                  ->orWhere('items.user_id', $userId);
            })
            ->whereNull('my_review.my_review_count')  // ← 修正ポイント
            ->select(
                'purchases.id as purchase_id',
                'items.name',
                'items.image',
                DB::raw('COALESCE(unreads.unread, 0) as unread'),
                DB::raw('last_msg.last_message_at')
            )
            ->orderByDesc('last_msg.last_message_at')
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
        $profile->postal_code   = $data['postal_code'];
        $profile->address_line1 = $data['address_line1'];
        $profile->address_line2 = $data['address_line2'] ?? null;

        if (isset($data['phone'])) {
            $profile->phone = $data['phone'];
        }
        if (isset($data['bio'])) {
            $profile->bio = $data['bio'];
        }

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
            'postal_code'   => ['required', 'string', 'size:8', 'regex:/^\d{3}-\d{4}$/'],
            'address_line1' => ['required', 'string', 'max:255'],
        ]);

        $user    = Auth::user();
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $profile->fill($data)->save();

        return redirect()->route('mypage.index');
    }
}