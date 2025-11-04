<?php

namespace App\Http\Controllers;

use App\Models\{Item, Like, Comment, Category, Condition};
use App\Http\Requests\ExhibitionRequest;
use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only([
            'mylist', 'create', 'store', 'storeComment',
            // like/unlike だけ残す
            'like', 'unlike',
        ]);
    }

    /** トップ（商品一覧：おすすめ/マイリスト切替） */
    public function index(Request $request)
    {
        if ($request->boolean('reset')) {
            session()->forget('q');
        } elseif ($request->filled('q')) {
            session(['q' => $request->query('q')]);
        } elseif ($request->filled('keyword')) {
            session(['q' => $request->query('keyword')]);
        }
        $keyword = session('q');
        $tab = (string) $request->query('tab', 'recommend');

        $category = ($request->filled('category') && is_numeric($request->query('category')))
            ? (int) $request->query('category')
            : null;

        if ($tab === 'mylist' && !Auth::check()) {
            return redirect('/login');
        }

        if ($tab === 'mylist') {
            $items = Auth::user()
                ->likedItems()
                ->select('items.*')
                ->when($keyword, function ($q, $kw) {
                    $q->where(function ($qq) use ($kw) {
                        $qq->where('items.name', 'like', "%{$kw}%")
                           ->orWhere('items.brand', 'like', "%{$kw}%")
                           ->orWhere('items.description', 'like', "%{$kw}%");
                    });
                })
                ->when(!is_null($category), function ($q) use ($category) {
                    $q->whereHas('categories', fn ($qq) => $qq->where('categories.id', $category));
                })
                ->orderByRaw("CASE WHEN items.status='on_sale' THEN 0 ELSE 1 END")
                ->orderBy('items.created_at', 'desc')
                ->paginate(12)
                ->withQueryString();

            return view('items.mylist', [
                'items'    => $items,
                'keyword'  => $keyword,
                'tab'      => 'mylist',
                'category' => $category,
            ]);
        }

        $items = Item::query()
            ->when($keyword, function ($q, $kw) {
                $q->where(function ($qq) use ($kw) {
                    $qq->where('name', 'like', "%{$kw}%")
                       ->orWhere('brand', 'like', "%{$kw}%")
                       ->orWhere('description', 'like', "%{$kw}%");
                });
            })
            ->when(Auth::check(), fn ($q) => $q->where('user_id', '!=', Auth::id()))
            ->when(!is_null($category), function ($q) use ($category) {
                $q->whereHas('categories', fn ($qq) => $qq->where('categories.id', $category));
            })
            ->orderByRaw("CASE WHEN status='on_sale' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('items.index', compact('items', 'keyword', 'tab', 'category'));
    }

    /** マイリスト（/mylist 専用） */
    public function mylist(Request $request)
    {
        $keyword = (string) $request->query('q', session('q'));
        $category = ($request->filled('category') && is_numeric($request->query('category')))
            ? (int) $request->query('category')
            : null;

        $items = auth()->user()
            ->likedItems()
            ->select('items.*')
            ->when($keyword, function ($q, $kw) {
                $q->where(function ($qq) use ($kw) {
                    $qq->where('items.name', 'like', "%{$kw}%")
                       ->orWhere('items.brand', 'like', "%{$kw}%")
                       ->orWhere('items.description', 'like', "%{$kw}%");
                });
            })
            ->when(!is_null($category), function ($q) use ($category) {
                $q->whereHas('categories', fn ($qq) => $qq->where('categories.id', $category));
            })
            ->orderByRaw("CASE WHEN items.status='on_sale' THEN 0 ELSE 1 END")
            ->orderBy('items.created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('items.mylist', [
            'items'    => $items,
            'keyword'  => $keyword,
            'tab'      => 'mylist',
            'category' => $category,
        ]);
    }

    /** 商品詳細 */
    public function show(Item $item)
    {
        $item->load([
            'categories',
            'comments' => fn ($q) => $q->latest()->with('user:id,name'),
        ]);

        $liked = auth()->check()
            ? Like::where('user_id', auth()->id())->where('item_id', $item->id)->exists()
            : false;

        return view('items.show', [
            'item'     => $item,
            'liked'    => $liked,
            'comments' => $item->comments,
        ]);
    }

    /** 出品フォーム */
    public function create()
    {
        $categories = Category::orderBy('sort')->orderBy('id')->get();
        $conditions = Condition::orderBy('id')->get();

        return view('items.create', compact('categories', 'conditions'));
    }

    /**
     * 出品登録
     * - N:N（category_item）に保存
     * - 後方互換のため items.category_id にも代表カテゴリを保存
     * - 入力は `category_id`（単数）/`category_ids[]`（複数）の両対応
     */
    public function store(ExhibitionRequest $request)
    {
        $data = $request->validated();

        // 画像保存（/storage/items/... の公開URL）
        $path     = $request->file('image_file')->store('items', 'public');
        $imageUrl = Storage::url($path);

        return DB::transaction(function () use ($request, $data, $imageUrl) {
            // ---- 代表カテゴリ決定 ----
            $primaryCategoryId =
                $data['category_id'] ??
                ($data['category_ids'][0] ?? null) ??
                ($request->filled('category_id') ? (int)$request->input('category_id') : null) ??
                (is_array($request->input('category_ids')) && count($request->input('category_ids')) > 0
                    ? (int)$request->input('category_ids')[0]
                    : null);

            if (is_null($primaryCategoryId)) {
                $primaryCategoryId = (int) Category::orderBy('id')->value('id');
            }

            // ---- Item 作成（items.category_id も保存）----
            $item = Item::create([
                'user_id'      => auth()->id(),
                'condition_id' => $data['condition_id'],
                'category_id'  => $primaryCategoryId,
                'name'         => $data['name'],
                'description'  => $data['description'],
                'brand'        => $data['brand'] ?? null,
                'image'        => $imageUrl,
                'price'        => $data['price'],
                'status'       => 'on_sale',
            ]);

            // ---- 多対多カテゴリの同期 ----
            $ids = $data['category_ids'] ?? null;

            if (is_null($ids) && isset($data['category_id'])) {
                $ids = [$data['category_id']];
            }
            if (is_null($ids) && $request->filled('category_id')) {
                $ids = [(int) $request->input('category_id')];
            }
            if (is_null($ids) && is_array($request->input('category_ids'))) {
                $ids = $request->input('category_ids');
            }

            if ($ids) {
                $ids = collect($ids)->map(fn ($v) => (int) $v)->filter()->unique()->values()->all();
                if (count($ids) === 0) {
                    $ids = [$primaryCategoryId];
                }
                $item->categories()->sync($ids);
            } else {
                $item->categories()->sync([$primaryCategoryId]);
            }

            return redirect()->route('items.show', $item)->with('status', '出品しました');
        });
    }

    /** いいね追加（POST /items/{item}/like） */
    public function like(Item $item)
    {
        $userId = auth()->id();

        DB::transaction(function () use ($item, $userId) {
            if (! $item->likes()->where('user_id', $userId)->exists()) {
                $item->likes()->create(['user_id' => $userId]);
                if ($item->isFillable('likes_count')) {
                    $item->increment('likes_count');
                }
            }
        });

        return back();
    }

    /** いいね解除（DELETE /items/{item}/unlike） */
    public function unlike(Item $item)
    {
        $userId = auth()->id();

        DB::transaction(function () use ($item, $userId) {
            $deleted = $item->likes()->where('user_id', $userId)->delete();
            if ($deleted && $item->isFillable('likes_count') && $item->likes_count > 0) {
                $item->decrement('likes_count');
            }
        });

        return back();
    }

    /** コメント投稿 */
    public function storeComment(CommentRequest $request, Item $item)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $item) {
            Comment::create([
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'body'    => $validated['body'],
            ]);
            $item->increment('comments_count');
        });

        return back()->with('status', 'コメントを投稿しました');
    }
}