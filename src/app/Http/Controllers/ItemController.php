<?php

namespace App\Http\Controllers;

use App\Models\{Item, Like, Comment, Category};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * トップ（商品一覧）
     * ?tab=mylist でマイリスト表示（PG01/PG02）
     * ?keyword= で検索（保持）
     */
    public function index(Request $request)
    {
        // 検索キーワード保持
        if ($request->filled('keyword')) {
            session(['keyword' => $request->keyword]);
        }
        $keyword = session('keyword');

        // ベースクエリ：販売中優先 → 新着
        $q = Item::query()
            ->when($keyword, function ($q, $kw) {
                $q->where(function ($qq) use ($kw) {
                    $qq->where('name', 'like', "%{$kw}%")
                       ->orWhere('brand', 'like', "%{$kw}%")
                       ->orWhere('description', 'like', "%{$kw}%");
                });
            })
            ->orderByRaw("CASE WHEN status='on_sale' THEN 0 ELSE 1 END")
            ->latest();

        // マイリストタブ
        if ($request->get('tab') === 'mylist' && Auth::check()) {
            $items = Auth::user()->likedItems()->getQuery()
                ->when($keyword, function ($q, $kw) {
                    $q->where(function ($qq) use ($kw) {
                        $qq->where('items.name', 'like', "%{$kw}%")
                           ->orWhere('items.brand', 'like', "%{$kw}%")
                           ->orWhere('items.description', 'like', "%{$kw}%");
                    });
                })
                ->orderBy('items.created_at', 'desc')
                ->paginate(12)
                ->withQueryString();

            return view('items.index', compact('items', 'keyword'));
        }

        $items = $q->paginate(12)->withQueryString();
        return view('items.index', compact('items', 'keyword'));
    }

    /**
     * 商品詳細（PG05）
     */
    public function show(Item $item)
    {
        $item->load([
            'category',
            'comments' => fn ($q) => $q->latest()->with('user:id,name'),
        ]);

        $liked = false;
        if (Auth::check()) {
            $liked = Like::where('user_id', Auth::id())
                ->where('item_id', $item->id)
                ->exists();
        }

        return view('items.show', compact('item', 'liked'));
    }

    /**
     * 出品画面（PG08）
     */
    public function create()
    {
        $categories = Category::orderBy('sort')->get();
        return view('items.create', compact('categories'));
    }

    /**
     * 出品登録
     * バリデーションは表（ExhibitionRequest）に準拠
     */
    public function store(Request $request)
    {
        $rules = [
            'name'        => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:255'],
            'brand'       => ['nullable', 'string', 'max:80'],
            'image'       => ['nullable', 'url'],         // URLで保存する場合
            'image_file'  => ['nullable','image','mimes:jpeg,png','max:4096'], // ファイルアップロードにも対応
            'category_id' => ['nullable', 'exists:categories,id'],
            'condition_id'=> ['required', 'integer', 'exists:conditions,id'],
            'price'       => ['required', 'integer', 'min:0'],
        ];
        $data = $request->validate($rules);

        // 画像：URL優先。無ければアップロードを保存
        if (empty($data['image']) && $request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('public/items');
            // 画面では <img src="{{ $item->image }}"> を想定 → storageの公開URLにするなら以下
            $data['image'] = Storage::url($path);
        }
        if (empty($data['image'])) {
            return back()->withErrors(['image' => '画像のURL もしくは 画像ファイルを指定してください'])->withInput();
        }

        $item = Item::create([
            'user_id'       => Auth::id(),
            'category_id'   => $data['category_id'] ?? null,
            'condition_id'  => $data['condition_id'],
            'name'          => $data['name'],
            'description'   => $data['description'],
            'brand'         => $data['brand'] ?? null,
            'image'         => $data['image'],
            'price'         => $data['price'],
            'status'        => 'on_sale',
        ]);

        return redirect()->route('items.show', $item)->with('status', '出品しました');
    }

    /**
     * いいねトグル（US005-FN018）
     */
    public function toggleLike(Item $item)
    {
        $user = Auth::user();

        $like = Like::where('user_id', $user->id)->where('item_id', $item->id)->first();
        if ($like) {
            $like->delete();
            $item->decrement('likes_count');
        } else {
            Like::create(['user_id' => $user->id, 'item_id' => $item->id]);
            $item->increment('likes_count');
        }
        return back();
    }

    /**
     * コメント送信（US006-FN020）
     */
    public function storeComment(Request $request, Item $item)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:255'],
        ]);

        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
            'body'    => $validated['body'],
        ]);

        $item->increment('comments_count');

        return back()->with('status', 'コメントを投稿しました');
    }
}