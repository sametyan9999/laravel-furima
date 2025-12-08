{{-- resources/views/trade/index.blade.php --}}
@extends('layouts.app')
@section('title', '取引チャット')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/trade.css') }}">
@endpush

@section('content')
<div class="trade-page">
  <div class="trade-wrapper">

    {{-- ▼ サイドバー（その他の取引） --}}
    <aside class="trade-sidebar">
      <h2 class="sidebar-title">その他の取引</h2>

      @foreach($sidebarTrades as $t)
        @if($t->id !== $purchase->id)
          <a href="{{ route('trade.show', $t->id) }}" class="sidebar-item">
            <div class="sidebar-item__inner">
              <div class="sidebar-item__thumb">
                <img src="{{ $t->image_url ?? $t->image }}" alt="{{ $t->name }}">
              </div>
              <div class="sidebar-item__name">
                {{ $t->name }}
              </div>
            </div>
          </a>
        @endif
      @endforeach
    </aside>

    {{-- ▼ メインコンテンツ --}}
    <section class="trade-main">

      @php
        // ログインユーザーが「購入者」か「出品者」かを判定
        $isBuyer  = ($purchase->user_id === $me->id);
        $isSeller = ($purchase->item->user_id === $me->id);
      @endphp

      {{-- 上部ヘッダー --}}
      <div class="trade-header">
        <div class="trade-header__left">
          <div class="avatar-circle avatar-circle--lg">
            @if(optional($otherUser->profile)->avatar_path)
              <img src="{{ asset($otherUser->profile->avatar_path) }}" alt="{{ $otherUser->name }}">
            @endif
          </div>
          <span class="trade-header__title">
            「{{ $otherUser->name }}」さんとの取引画面
          </span>
        </div>

        {{-- ★ 取引完了ボタンは「購入者」だけに表示 --}}
        @if(!$purchase->is_done && $isBuyer)
          <form method="post" action="{{ route('trade.finish', $purchase) }}">
            @csrf
            <button class="btn-finish" type="submit">取引を完了する</button>
          </form>
        @endif
      </div>

      {{-- 商品情報ブロック --}}
      <div class="trade-item-box">
        <div class="trade-item-img">
          <div class="trade-item-img__inner">
            <img src="{{ $item->image_url ?? $item->image }}" alt="{{ $item->name }}">
          </div>
        </div>
        <div class="trade-item-info">
          <h2 class="trade-item-name">{{ $item->name }}</h2>
          <p class="trade-item-price">¥{{ number_format($item->price) }}</p>
        </div>
      </div>

      {{-- 取引完了後の評価モーダル --}}
      @if (
        // 購入者：取引完了ボタン押下直後のみ
        ($isBuyer && session('review_modal') && !$alreadyReviewed)
        ||
        // 出品者：購入者が完了済み & 自分は未評価のとき
        ($isSeller && $purchase->is_done && !$alreadyReviewed)
      )
        <div class="trade-review-modal">
          <div class="trade-review-modal__inner">
            <p class="trade-review-modal__title">取引が完了しました。</p>
            <p class="trade-review-modal__text">今回の取引相手はいかがでしたか？</p>

            <form method="post" action="{{ route('trade.review.store', $purchase) }}">
              @csrf

              <div class="trade-review-modal__stars" id="rating-stars">
                @for ($i = 1; $i <= 5; $i++)
                  <span class="star"
                        data-value="{{ $i }}"
                        style="font-size:32px; cursor:pointer; color:#ccc;">
                    ★
                  </span>
                @endfor
              </div>

              <input type="hidden" name="rating" id="rating-input" value="3">

              <div class="trade-review-modal__actions">
                <button type="submit" class="trade-review-modal__submit">送信する</button>
              </div>
            </form>
          </div>
        </div>

        <script>
          document.addEventListener('DOMContentLoaded', () => {
            const stars = document.querySelectorAll('#rating-stars .star');
            const ratingInput = document.getElementById('rating-input');

            const updateStars = (value) => {
              stars.forEach(star => {
                const v = parseInt(star.dataset.value);
                star.style.color = v <= value ? '#FFD700' : '#ccc';
              });
            };

            // 初期値：★3
            updateStars(3);

            stars.forEach(star => {
              star.addEventListener('click', () => {
                const value = parseInt(star.dataset.value);
                ratingInput.value = value;
                updateStars(value);
              });
            });
          });
        </script>
      @endif

      {{-- メッセージ一覧 --}}
      <div class="trade-message-list">
        @foreach($messages as $msg)

          @if($msg->user_id === $me->id)
            {{-- 自分 --}}
            <div class="trade-message trade-message--me">
              <div class="trade-msg-header trade-msg-header--me">
                <span class="trade-msg-username">{{ $me->name }}</span>
                <div class="avatar-circle avatar-circle--sm">
                  @if(optional($me->profile)->avatar_path)
                    <img src="{{ asset($me->profile->avatar_path) }}" alt="{{ $me->name }}">
                  @endif
                </div>
              </div>

              <div class="trade-msg-body">
                @if(!$msg->is_deleted)
                  @if($msg->body)
                    <div class="trade-msg-text trade-msg-text--me">{{ $msg->body }}</div>
                  @endif
                  @if($msg->image_path)
                    <img src="{{ asset('storage/' . $msg->image_path) }}" class="trade-msg-image" alt="添付画像">
                  @endif
                @else
                  <div class="trade-msg-deleted">このメッセージは削除されました</div>
                @endif
              </div>

              @if(!$msg->is_deleted)
                <div class="trade-msg-actions">
                  <a href="{{ route('trade.show', ['purchase' => $purchase->id, 'edit' => $msg->id]) }}" class="link-btn">編集</a>
                  <form action="{{ route('trade.delete', [$purchase, $msg]) }}" method="post">
                    @csrf
                    @method('delete')
                    <button type="submit" class="link-btn">削除</button>
                  </form>
                </div>
              @endif
            </div>

          @else
            {{-- 相手 --}}
            <div class="trade-message trade-message--other">
              <div class="trade-msg-header">
                <div class="avatar-circle avatar-circle--sm">
                  @if(optional($otherUser->profile)->avatar_path)
                    <img src="{{ asset($otherUser->profile->avatar_path) }}" alt="{{ $otherUser->name }}">
                  @endif
                </div>
                <span class="trade-msg-username">{{ $otherUser->name }}</span>
              </div>

              <div class="trade-msg-body">
                @if(!$msg->is_deleted)
                  @if($msg->body)
                    <div class="trade-msg-text">{{ $msg->body }}</div>
                  @endif
                  @if($msg->image_path)
                    <img src="{{ asset('storage/' . $msg->image_path) }}" class="trade-msg-image" alt="添付画像">
                  @endif
                @else
                  <div class="trade-msg-deleted">このメッセージは削除されました</div>
                @endif
              </div>
            </div>
          @endif

        @endforeach
      </div>

      {{-- ▼ メッセージ入力フォーム直上のバリデーションメッセージ --}}
      @if ($errors->any())
        <div class="trade-form-error">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- メッセージ入力フォーム --}}
      <form method="post"
            action="{{ route('trade.store', $purchase) }}"
            enctype="multipart/form-data"
            class="trade-form">
        @csrf

        @if(!empty($editingMessage))
          <input type="hidden" name="message_id" value="{{ $editingMessage->id }}">
        @endif

        <input type="text"
               name="body"
               class="trade-form-input"
               placeholder="取引メッセージを記入してください"
               value="{{ old('body', optional($editingMessage)->body) }}">

        <div class="trade-form-actions">
          <label class="trade-form-image-btn">
            画像を追加
            <input type="file" name="image" class="trade-form-image-input">
          </label>

          <button class="trade-send-btn" type="submit">
            <img src="{{ asset('images/icon-send.svg') }}" alt="送信">
          </button>
        </div>
      </form>

    </section>
  </div>
</div>
@endsection