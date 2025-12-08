{{-- resources/views/emails/trade_completed.blade.php --}}
@php
  $item = $purchase->item;
  $buyer = $purchase->user;
  $url = route('trade.show', $purchase);  // 取引チャット画面
@endphp

<p>{{ $buyer->name }} さんとの「{{ $item->name }}」の取引が完了しました。</p>

<p>取引相手の評価を行うには、下のリンクから取引チャット画面を開いてください。</p>

<p>
  <a href="{{ $url }}">取引チャットを開く</a>
</p>

<p>※このメールはシステムから自動送信されています。</p>