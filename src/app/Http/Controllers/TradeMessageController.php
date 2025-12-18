<?php

namespace App\Http\Controllers;

use App\Http\Requests\TradeMessageRequest;
use App\Models\Purchase;
use App\Models\TradeMessage;
use App\Models\TradeReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TradeMessageController extends Controller
{
    public function index(\Illuminate\Http\Request $request, Purchase $purchase)
    {
        $this->authorizeAccess($purchase);

        $me   = Auth::user();
        $item = $purchase->item;

        $otherUser = $purchase->user_id === $me->id
            ? $item->user
            : $purchase->user;

        $editingMessage = null;
        if ($editId = $request->query('edit')) {
            $editingMessage = TradeMessage::where('purchase_id', $purchase->id)
                ->where('id', $editId)
                ->where('user_id', $me->id)
                ->first();
        }

        // ▼ 既読更新（未読件数＝未読）
        if ($purchase->user_id === $me->id) {
            $purchase->buyer_read_at = now();
            $purchase->save();
        } elseif ($purchase->item->user_id === $me->id) {
            $purchase->seller_read_at = now();
            $purchase->save();
        }

        $userId = (int) $me->id;

        $unreadSub = DB::table('trade_messages as tm')
            ->join('purchases as p', 'tm.purchase_id', '=', 'p.id')
            ->join('items as i', 'p.item_id', '=', 'i.id')
            ->select('tm.purchase_id', DB::raw('COUNT(*) as unread'))
            ->where('tm.is_deleted', 0)
            ->where('tm.user_id', '!=', $userId)
            ->where(function ($q) use ($userId) {

                $q->where(function ($q2) use ($userId) {
                    $q2->where('p.user_id', $userId)
                       ->where(function ($q3) {
                           $q3->whereColumn('tm.created_at', '>', 'p.buyer_read_at')
                              ->orWhereNull('p.buyer_read_at');
                       });
                })
                ->orWhere(function ($q2) use ($userId) {
                    $q2->where('i.user_id', $userId)
                       ->where(function ($q3) {
                           $q3->whereColumn('tm.created_at', '>', 'p.seller_read_at')
                              ->orWhereNull('p.seller_read_at');
                       });
                });
            })
            ->groupBy('tm.purchase_id');

        $lastMessageSub = DB::table('trade_messages as tm_all')
            ->select('tm_all.purchase_id', DB::raw('MAX(tm_all.created_at) as last_message_at'))
            ->where('tm_all.is_deleted', 0)
            ->groupBy('tm_all.purchase_id');

        $myReviewSub = DB::table('trade_reviews')
            ->select('purchase_id', DB::raw('COUNT(*) as my_review_count'))
            ->where('reviewer_id', $userId)
            ->groupBy('purchase_id');

        $sidebarTrades = DB::table('purchases')
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
            ->whereNull('my_review.my_review_count')
            ->select(
                'purchases.id as id',
                'items.name as name',
                'items.image as image',
                DB::raw('COALESCE(unreads.unread, 0) as unread'),
                DB::raw('last_msg.last_message_at as last_message_at')
            )
            ->orderByDesc('last_msg.last_message_at')
            ->get()
            ->map(function ($row) {
                $image = $row->image ?? null;

                $imageUrl = null;
                if ($image) {
                    if (preg_match('#^https?://#', $image) || str_starts_with($image, '/storage/')) {
                        $imageUrl = $image;
                    } elseif (str_starts_with($image, 'public/')) {
                        $imageUrl = '/storage/' . substr($image, 7);
                    } else {
                        $imageUrl = \Illuminate\Support\Facades\Storage::url($image);
                    }
                }

                $row->image_url = $imageUrl;
                return $row;
            });

        $messages = TradeMessage::with('user:id,name')
            ->where('purchase_id', $purchase->id)
            ->orderBy('created_at')
            ->get();

        $alreadyReviewed = TradeReview::where('purchase_id', $purchase->id)
            ->where('reviewer_id', $me->id)
            ->exists();

        $buyerReviewed = TradeReview::where('purchase_id', $purchase->id)
            ->where('reviewer_id', $purchase->user_id)
            ->exists();

        return view('trade.index', compact(
            'purchase',
            'item',
            'messages',
            'me',
            'otherUser',
            'sidebarTrades',
            'editingMessage',
            'alreadyReviewed',
            'buyerReviewed'
        ));
    }

    public function store(TradeMessageRequest $request, Purchase $purchase)
    {
        $this->authorizeAccess($purchase);

        $data = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('trade_messages', 'public');
        }

        $messageId = $request->input('message_id');

        if ($messageId) {
            $message = TradeMessage::where('purchase_id', $purchase->id)
                ->where('id', $messageId)
                ->firstOrFail();

            if ($message->user_id !== Auth::id()) abort(403);

            $update = ['body' => $data['body']];
            if ($imagePath !== null) $update['image_path'] = $imagePath;

            $message->update($update);

        } else {
            TradeMessage::create([
                'user_id'     => Auth::id(),
                'purchase_id' => $purchase->id,
                'body'        => $data['body'],
                'image_path'  => $imagePath,
            ]);
        }

        return redirect()->route('trade.show', $purchase);
    }

    public function destroy(Purchase $purchase, TradeMessage $message)
    {
        $this->authorizeAccess($purchase);

        if ($message->user_id !== Auth::id()) abort(403);

        $message->update(['is_deleted' => true]);

        return back();
    }

    public function finish(Purchase $purchase)
    {
        $this->authorizeAccess($purchase);

        $userId = Auth::id();

        $isBuyer  = ($purchase->user_id === $userId);
        $isSeller = ($purchase->item->user_id === $userId);

        if (!$isBuyer && !$isSeller) abort(403);

        $alreadyReviewed = TradeReview::where('purchase_id', $purchase->id)
            ->where('reviewer_id', $userId)
            ->exists();

        if (!$alreadyReviewed) {
            session()->flash('review_modal', true);
        }

        return redirect()->route('trade.show', $purchase);
    }

    private function authorizeAccess(Purchase $purchase): void
    {
        $uid = Auth::id();

        if ($purchase->user_id !== $uid &&
            $purchase->item->user_id !== $uid) {
            abort(403);
        }
    }
}