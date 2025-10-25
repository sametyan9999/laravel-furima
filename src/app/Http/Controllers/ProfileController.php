<?php

namespace App\Http\Controllers;

use App\Models\{Item, Purchase, Profile};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * プロフィール画面（PG09）
     * /mypage?page=buy|sell でタブ切替（PG11/PG12）
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page'); // buy | sell | null

        $profile = $user->profile;
        $bought  = null;
        $sold    = null;

        if ($page === 'buy') {
            $bought = Purchase::with('item')->where('buyer_user_id', $user->id)
                ->latest('purchased_at')->get();
        } elseif ($page === 'sell') {
            $sold = $user->items()->latest()->get();
        } else {
            // デフォルトは出品した商品
            $sold = $user->items()->latest()->get();
        }

        return view('mypage.index', compact('user','profile','bought','sold','page'));
    }

    /**
     * プロフィール編集（PG10）
     */
    public function edit()
    {
        $profile = Auth::user()->profile;
        return view('mypage.profile', compact('profile'));
    }

    /**
     * プロフィール更新
     *  - avatar: jpeg/png
     *  - username max:20（users.name を更新）
     *  - postal_code サイズ8（123-4567）
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
            'avatar'        => ['nullable','image','mimes:jpeg,png','max:4096'],
        ]);

        // ユーザー名（Blade 側の name="username" と一致させる）
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
            $path = $request->file('avatar')->store('public/avatars');
            $profile->avatar_path = \Storage::url($path);
        }

        $profile->save();

        return redirect()->route('mypage.index')->with('status', 'プロフィールを更新しました');
    }

    /**
     * 初回プロフィール設定（任意：FN006）
     */
    public function first()
    {
        $profile = Auth::user()->profile;
        return view('mypage.profile-first', compact('profile'));
    }

    public function storeFirst(Request $request)
    {
        // 最低限：郵便番号と住所だけ
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