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

        // ← ここを互換APIに変更
        $view = (string) ($request->query('view')); // 'buy' | 'sell' | null

        $profile = $user->profile;
        $bought  = null;
        $sold    = null;

        if ($view === 'buy') {
            // 購入履歴（PG11）
            $bought = Purchase::with('item')
                ->where('buyer_user_id', $user->id)
                ->latest('purchased_at')
                ->paginate(12)
                ->withQueryString();
        } else {
            // 出品一覧（PG12, 既定）
            $sold = $user->items()
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
     * - avatar: jpeg/png を public ディスクに保存（/storage/avatars/...）
     * - username max:20（users.name）
     * - postal_code サイズ8（123-4567）
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

        // アバター保存（storage:link 前提）
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar_path = '/storage/' . $path;
        }

        $profile->save();

        return redirect()->route('mypage.index')->with('status', 'プロフィールを更新しました');
    }

    /** 初回プロフィール設定（任意：FN006） */
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
