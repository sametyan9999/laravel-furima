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
                <img src="{{ $t->image }}" alt="{{ $t->name }}">
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

        @if(!$purchase->is_done)
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
          <p class="trade-item-price">
            ¥{{ number_format($item->price) }}
          </p>
        </div>
      </div>

      {{-- エラーメッセージ（入力欄の上に表示） --}}
      @if ($errors->any())
        <div class="trade-error-box">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- メッセージ一覧 --}}
      <div class="trade-message-list">
        @foreach($messages as $msg)

          @if($msg->user_id === $me->id)
            {{-- 自分のメッセージ --}}
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
                    <img src="{{ asset($msg->image_path) }}" class="trade-msg-image" alt="添付画像">
                  @endif
                @else
                  <div class="trade-msg-deleted">このメッセージは削除されました</div>
                @endif
              </div>

              @if(!$msg->is_deleted)
                <div class="trade-msg-actions">
                  {{-- ★ 編集リンク：同じ画面を編集モードで開く --}}
                  <a href="{{ route('trade.show', ['purchase' => $purchase->id, 'edit' => $msg->id]) }}"
                     class="link-btn">
                    編集
                  </a>

                  <form action="{{ route('trade.delete', [$purchase, $msg]) }}"
                        method="post">
                    @csrf
                    @method('delete')
                    <button type="submit" class="link-btn">削除</button>
                  </form>
                </div>
              @endif
            </div>

          @else
            {{-- 相手のメッセージ --}}
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
                    <img src="{{ asset($msg->image_path) }}" class="trade-msg-image" alt="添付画像">
                  @endif
                @else
                  <div class="trade-msg-deleted">このメッセージは削除されました</div>
                @endif
              </div>
            </div>
          @endif

        @endforeach
      </div>

      {{-- メッセージ入力フォーム（新規 & 編集兼用） --}}
      <form method="post"
            action="{{ route('trade.store', $purchase) }}"
            enctype="multipart/form-data"
            class="trade-form">
        @csrf

        {{-- 編集モードのときだけ hidden で message_id を送る --}}
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