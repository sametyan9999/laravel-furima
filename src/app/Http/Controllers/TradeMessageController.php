<?php

namespace App\Http\Controllers;

use App\Http\Requests\TradeMessageRequest;
use App\Models\Purchase;
use App\Models\TradeMessage;
use App\Models\TradeReview;
use Illuminate\Support\Facades\Auth;

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

        $sidebarTrades = Purchase::with('item')
            ->where(function ($q) use ($me) {
                $q->where('user_id', $me->id)
                  ->orWhereHas('item', fn($q2) => $q2->where('user_id', $me->id));
            })
            ->leftJoin('trade_reviews as r', function ($join) use ($me) {
                $join->on('purchases.id', '=', 'r.purchase_id')
                     ->where('r.reviewer_id', $me->id);
            })
            ->whereNull('r.id')
            ->orderByDesc(
                TradeMessage::selectRaw('MAX(trade_messages.created_at)')
                    ->whereColumn('trade_messages.purchase_id', 'purchases.id')
            )
            ->select('purchases.*')
            ->get()
            ->map(function ($p) {
                $item = $p->item;
                return (object)[
                    'id'        => $p->id,
                    'name'      => $item?->name ?? '',
                    'image_url' => $item?->image_url ?? null,
                    'image'     => $item?->image ?? '',
                ];
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