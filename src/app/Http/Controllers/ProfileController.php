<?php

namespace App\Http\Controllers;

use App\Models\{Item, Purchase, Profile};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * マイページ（PG09/PG11/PG12）
     * /mypage?view=buy|sell でタブ切替（デフォルト：sell）
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // タブ判定（sell を既定に）
        $view = (string) $request->query('view', 'sell'); // 'buy' | 'sell'

        $profile = $user->profile;
        $bought  = null;
        $sold    = null;

        if ($view === 'buy') {
            // ✅ purchases.user_id（購入者）で検索
            $bought = Purchase::with('item')
                ->where('user_id', $user->id)
                ->latest('purchased_at')
                ->paginate(12)
                ->withQueryString();
        } else {
            // 出品一覧
            $sold = Item::where('user_id', $user->id)
                ->latest()
                ->paginate(12)
                ->withQueryString();
            $view = 'sell';
        }

        return view('mypage.index', compact('user', 'profile', 'bought', 'sold', 'view'));
    }

    /** プロフィール編集（PG10） */
    public function edit()
    {
        $profile = Auth::user()->profile;
        return view('mypage.profile', compact('profile'));
    }

    /**
     * プロフィール更新（PG10）
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'username'      => ['required','string','max:20'],
            'postal_code'   => ['required','string','size:8','regex:/^\d{3}-\d{4}$/'],
            'address_line1' => ['required','string','max:255'],
            'address_line2' => ['nullable','string','max:255'],
            'phone'         => ['nullable','string','max:20'],
            'bio'           => ['nullable','string','max:255'],
            'avatar'        => ['nullable','image','mimes:jpeg,png','max:10240'],
        ]);

        // ユーザー名
        $user->name = $data['username'];
        $user->save();

        // プロフィール
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $profile->postal_code   = $data['postal_code'];
        $profile->address_line1 = $data['address_line1'];
        $profile->address_line2 = $data['address_line2'] ?? null;
        $profile->phone         = $data['phone'] ?? null;
        $profile->bio           = $data['bio'] ?? null;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar_path = '/storage/' . $path;
        }

        $profile->save();

        return redirect()->route('mypage.index')->with('status', 'プロフィールを更新しました');
    }

    /** 初回プロフィール設定（任意） */
    public function first()
    {
        $profile = Auth::user()->profile;
        return view('mypage.profile-first', compact('profile'));
    }

    public function storeFirst(Request $request)
    {
        $data = $request->validate([
            'postal_code'   => ['required','string','size:8','regex:/^\d{3}-\d{4}$/'],
            'address_line1' => ['required','string','max:255'],
        ]);

        $user = Auth::user();
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $profile->fill($data)->save();

        return redirect()->route('mypage.index');
    }
}