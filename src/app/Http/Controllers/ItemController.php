<?php

namespace App\Http\Controllers;

use App\Models\{Item, Like, Comment, Category, Condition};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /** トップ（商品一覧：PG01/PG02） */
    public function index(Request $request)
    {
        // ---- 検索キーワードを q に統一（keyword 互換維持）----
        if ($request->filled('q')) {
            session(['q' => $request->query('q')]);
        } elseif ($request->filled('keyword')) {
            session(['q' => $request->query('keyword')]);
        }
        $keyword  = session('q');

        $tab      = (string) ($request->query('tab', 'recommend')); // 'recommend' | 'mylist'
        $category = (int) ($request->query('category'));            // カテゴリID（任意）

        // ---- マイリストタブ（互換表示） ----
        if ($tab === 'mylist' && Auth::check()) {
            $items = Auth::user()->likedItems()->getQuery()
                ->when($keyword, function ($q, $kw) {
                    $q->where(function ($qq) use ($kw) {
                        $qq->where('items.name', 'like', "%{$kw}%")
                           ->orWhere('items.brand', 'like', "%{$kw}%")
                           ->orWhere('items.description', 'like', "%{$kw}%");
                    });
                })
                ->when($category, fn ($q, $cid) => $q->where('items.category_id', $cid))
                ->orderByRaw("CASE WHEN items.status='on_sale' THEN 0 ELSE 1 END")
                ->orderBy('items.created_at', 'desc')
                ->paginate(12)
                ->withQueryString();

            return view('items.mylist', [
                'items'   => $items,
                'keyword' => $keyword,
                'tab'     => 'mylist',
                'category'=> $category,
            ]);
        }

        // ---- おすすめ（全体） ----
        $items = Item::query()
            ->when($keyword, function ($q, $kw) {
                $q->where(function ($qq) use ($kw) {
                    $qq->where('name', 'like', "%{$kw}%")
                       ->orWhere('brand', 'like', "%{$kw}%")
                       ->orWhere('description', 'like', "%{$kw}%");
                });
            })
            ->when($category, fn ($q, $cid) => $q->where('category_id', $cid))
            ->orderByRaw("CASE WHEN status='on_sale' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('items.index', compact('items', 'keyword', 'tab', 'category'));
    }

    /** ⑤：/mylist（いいね一覧 専用ルート） */
    public function mylist(Request $request)
    {
        $keyword  = (string) $request->query('q', session('q'));
        $category = (int) $request->query('category');

        $items = auth()->user()->likedItems()->getQuery()
            ->when($keyword, function ($q, $kw) {
                $q->where(function ($qq) use ($kw) {
                    $qq->where('items.name', 'like', "%{$kw}%")
                       ->orWhere('items.brand', 'like', "%{$kw}%")
                       ->orWhere('items.description', 'like', "%{$kw}%");
                });
            })
            ->when($category, fn ($q, $cid) => $q->where('items.category_id', $cid))
            ->orderByRaw("CASE WHEN items.status='on_sale' THEN 0 ELSE 1 END")
            ->orderBy('items.created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('items.mylist', [
            'items'   => $items,
            'keyword' => $keyword,
            'tab'     => 'mylist',
            'category'=> $category,
        ]);
    }

    /** 商品詳細（PG05） */
    public function show(Item $item)
    {
        $item->load([
            'categories',
            'comments' => fn ($q) => $q->latest()->with('user:id,name'),
        ]);

        $liked = Auth::check()
            ? Like::where('user_id', Auth::id())->where('item_id', $item->id)->exists()
            : false;

        $comments = $item->comments;
        return view('items.show', compact('item', 'liked', 'comments'));
    }

    /** 出品画面（PG08） */
    public function create()
    {
        $categories = Category::orderBy('sort')->orderBy('id')->get();
        $conditions = Condition::orderBy('id')->get();

        return view('items.create', compact('categories', 'conditions'));
    }

    /** 出品登録（PG08） */
    public function store(Request $request)
    {
        $rules = [
            'name'           => ['required', 'string', 'max:120'],
            'description'    => ['required', 'string', 'max:255'],
            'brand'          => ['nullable', 'string', 'max:80'],
            'image_file'     => ['required', 'image', 'mimes:jpeg,png', 'max:4096'],
            'category_ids'   => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'condition_id'   => ['required', 'integer', 'exists:conditions,id'],
            'price'          => ['required', 'integer', 'min:0'],
        ];
        $data = $request->validate($rules);

        $path     = $request->file('image_file')->store('items', 'public');
        $imageUrl = Storage::url($path);
        $firstCategoryId = (int) collect($data['category_ids'])->first();

        return DB::transaction(function () use ($data, $imageUrl, $firstCategoryId) {
            $item = Item::create([
                'user_id'      => Auth::id(),
                'category_id'  => $firstCategoryId,
                'condition_id' => $data['condition_id'],
                'name'         => $data['name'],
                'description'  => $data['description'],
                'brand'        => $data['brand'] ?? null,
                'image'        => $imageUrl,
                'price'        => $data['price'],
                'status'       => 'on_sale',
            ]);
            $item->categories()->sync($data['category_ids']);
            return redirect()->route('items.show', $item)->with('status', '出品しました');
        });
    }

    /** いいねトグル（US005-FN018） */
    public function toggleLike(Item $item)
    {
        $user = Auth::user();

        DB::transaction(function () use ($user, $item) {
            $like = Like::where('user_id', $user->id)->where('item_id', $item->id)->first();
            if ($like) {
                $like->delete();
                $item->decrement('likes_count');
            } else {
                Like::firstOrCreate(['user_id' => $user->id, 'item_id' => $item->id]);
                $item->increment('likes_count');
            }
        });

        return back();
    }

    /** コメント送信（US006-FN020） */
    public function storeComment(Request $request, Item $item)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $item) {
            Comment::create([
                'user_id' => Auth::id(),
                'item_id' => $item->id,
                'body'    => $validated['body'],
            ]);
            $item->increment('comments_count');
        });

        return back()->with('status', 'コメントを投稿しました');
    }
}
