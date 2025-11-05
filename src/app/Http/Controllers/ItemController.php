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
            'mylist', 'create', 'store', 'storeComment', 'like', 'unlike',
        ]);
    }

    /** トップ（商品一覧：おすすめ/マイリスト切替） */
    public function index(Request $request)
    {
        $keyword = $this->manageSearchSession($request);
        $tab = (string) $request->query('tab', 'recommend');
        $categoryId = $this->parseCategoryId($request);

        if ($tab === 'mylist' && !Auth::check()) {
            return redirect('/login');
        }

        if ($tab === 'mylist') {
            $items = $this->getMylistItems($keyword, $categoryId);
            // ✅ /?tab=mylist では専用ビューを返す（テスト: 見出し「いいね済み商品」を期待）
            return view('items.mylist', [
                'items'    => $items,
                'keyword'  => $keyword,
                'tab'      => 'mylist',
                'category' => $categoryId,
            ]);
        }

        $items = $this->getRecommendItems($keyword, $categoryId);

        return view('items.index', [
            'items'    => $items,
            'keyword'  => $keyword,
            'tab'      => 'recommend',
            'category' => $categoryId,
        ]);
    }

    /** マイリスト（/items/mylist 用：テストが直接叩く） */
    public function mylist(Request $request)
    {
        $keyword    = (string) $request->query('q', session('q'));
        $categoryId = $this->parseCategoryId($request);
        $items      = $this->getMylistItems($keyword, $categoryId);

        return view('items.mylist', [
            'items'    => $items,
            'keyword'  => $keyword,
            'tab'      => 'mylist',
            'category' => $categoryId,
        ]);
    }

    /** 検索セッション管理 */
    private function manageSearchSession(Request $request): ?string
    {
        if ($request->boolean('reset')) {
            session()->forget('q');
        } elseif ($request->filled('q')) {
            session(['q' => $request->query('q')]);
        } elseif ($request->filled('keyword')) {
            session(['q' => $request->query('keyword')]);
        }
        return session('q');
    }

    /** カテゴリIDをパース */
    private function parseCategoryId(Request $request): ?int
    {
        return ($request->filled('category') && is_numeric($request->query('category')))
            ? (int) $request->query('category')
            : null;
    }

    /** おすすめ商品一覧取得 */
    private function getRecommendItems(?string $keyword, ?int $categoryId)
    {
        return Item::query()
            ->when($keyword, fn($q) => $this->applyKeywordFilter($q, $keyword))
            ->when(Auth::check(), fn($q) => $q->where('user_id', '!=', Auth::id()))
            ->when($categoryId, fn($q) => $q->whereHas('categories', fn($qq) => $qq->where('categories.id', $categoryId)))
            ->orderByRaw("CASE WHEN status='on_sale' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    /** マイリスト商品一覧取得 */
    private function getMylistItems(?string $keyword, ?int $categoryId)
    {
        return Auth::user()
            ->likedItems()
            ->select('items.*')
            ->when($keyword, fn($q) => $this->applyKeywordFilter($q, $keyword, 'items.'))
            ->when($categoryId, fn($q) => $q->whereHas('categories', fn($qq) => $qq->where('categories.id', $categoryId)))
            ->orderByRaw("CASE WHEN items.status='on_sale' THEN 0 ELSE 1 END")
            ->orderBy('items.created_at', 'desc')
            ->paginate(12)
            ->withQueryString();
    }

    /** キーワード検索適用 */
    private function applyKeywordFilter($query, string $keyword, string $prefix = '')
    {
        $query->where(function ($q) use ($keyword, $prefix) {
            $q->where("{$prefix}name", 'like', "%{$keyword}%")
              ->orWhere("{$prefix}brand", 'like', "%{$keyword}%")
              ->orWhere("{$prefix}description", 'like', "%{$keyword}%");
        });
    }

    /** 商品詳細 */
    public function show(Item $item)
    {
        $item->load([
            'categories',
            'comments' => fn($q) => $q->latest()->with('user:id,name'),
        ]);

        $liked = Auth::check()
            ? Like::where('user_id', Auth::id())->where('item_id', $item->id)->exists()
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
     * - items.category_id（代表カテゴリ）にも保存
     * - 多対多（category_item）にも保存
     */
    public function store(ExhibitionRequest $request)
    {
        $data = $request->validated();

        // 画像
        $file    = $request->file('image_file') ?? $request->file('image');
        $path    = $file ? $file->store('items', 'public') : null;
        $imageUrl= $path ? Storage::url($path) : '/storage/items/sample.jpg';

        return DB::transaction(function () use ($request, $data, $imageUrl) {
            // 代表カテゴリ決定
            $ids = $data['category_ids'] ?? [$data['category_id'] ?? null];
            $ids = collect($ids)->filter()->unique()->values()->all();
            if (empty($ids)) {
                $ids = [(int) Category::orderBy('id')->value('id')];
            }
            $ids = array_map('intval', $ids);
            $primaryCategoryId = $ids[0];

            // Item 作成（代表カテゴリも保存）
            $item = Item::create([
                'user_id'      => Auth::id(),
                'condition_id' => $data['condition_id'],
                'category_id'  => $primaryCategoryId,
                'name'         => $data['name'],
                'description'  => $data['description'],
                'brand'        => $data['brand'] ?? null,
                'image'        => $imageUrl,
                'price'        => $data['price'],
                'status'       => 'on_sale',
            ]);

            // 多対多カテゴリ保存
            $item->categories()->sync($ids);

            return redirect()->route('items.show', $item)->with('status', '出品しました');
        });
    }

    /** いいね追加 */
    public function like(Item $item)
    {
        $userId = Auth::id();

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

    /** いいね解除 */
    public function unlike(Item $item)
    {
        $userId = Auth::id();

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
                'user_id' => Auth::id(),
                'item_id' => $item->id,
                'body'    => $validated['body'],
            ]);
            if ($item->isFillable('comments_count')) {
                $item->increment('comments_count');
            }
        });

        return back()->with('status', 'コメントを投稿しました');
    }
}