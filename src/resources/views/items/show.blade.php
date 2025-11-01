@extends('layouts.app')
@section('title', $item->name)

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/items.css') }}">
@endpush

@section('content')
<div class="detail">
  <div class="detail__left">
    @if($item->image_url)
      <img class="detail__image" src="{{ $item->image_url }}" alt="{{ $item->name }}">
    @endif
  </div>

  <div class="detail__right">
    <h1 class="detail__title">{{ $item->name }}</h1>
    <div class="detail__brand">{{ $item->brand ?? 'ブランド名' }}</div>

    <div class="detail__price">
      ¥{{ number_format($item->price) }} <small>（税込）</small>
    </div>

    <div class="detail__icons" aria-label="ステータス">
      @auth
        <form method="POST" action="{{ route('items.like', $item) }}" style="display:inline;">
          @csrf
          <button type="submit" class="icon-like {{ $liked ? 'is-liked' : '' }}" aria-pressed="{{ $liked ? 'true' : 'false' }}">★</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="icon-like">★</a>
      @endauth
      <small class="ml-8">{{ $item->likes_count }}</small>

      <span class="ml-16" aria-hidden="true">💬</span>
      <small class="ml-8">{{ $item->comments_count }}</small>
    </div>

    <a href="{{ route('purchase.index', $item) }}" class="gt-btn gt-btn--buy mt-16">購入手続きへ</a>

    {{-- ======================== --}}
    {{-- 商品説明 --}}
    {{-- ======================== --}}
    <section class="detail__section">
      <h2>商品説明</h2>
      <p class="detail__desc">{{ $item->description ?? 'カラー：グレー / 新品 / 即発送' }}</p>
    </section>

    {{-- ======================== --}}
    {{-- 商品の情報（カテゴリ・状態） --}}
    {{-- ======================== --}}
    <section class="detail__section">
      <h2>商品の情報</h2>

      {{-- カテゴリー：チップ表示 --}}
      <div class="detail-kv">
        <div class="detail-kv__label">カテゴリー</div>
        <div class="detail-kv__value">
          <div class="chips">
            @forelse($item->categories as $cat)
              <span class="chip">{{ $cat->name }}</span>
            @empty
              <span class="chip">{{ $item->category->name ?? 'カテゴリ未設定' }}</span>
            @endforelse
          </div>
        </div>
      </div>

      {{-- 商品の状態：文字のみ --}}
      <div class="detail-kv">
        <div class="detail-kv__label">商品の状態</div>
        <div class="detail-kv__value">{{ $item->condition->name ?? '状態未設定' }}</div>
      </div>
    </section>

    {{-- ======================== --}}
    {{-- コメントセクション --}}
    {{-- ======================== --}}
    <section class="detail__section">
      <h2>コメント（{{ $comments->count() }}）</h2>

      @forelse($comments as $c)
        <div class="comment">
          <div class="avatar"></div>
          <div class="comment__meta">
            <div class="comment__name">{{ $c->user->name ?? 'ゲスト' }}</div>
          </div>
        </div>
        <div class="comment__bubble">{{ $c->body }}</div>
      @empty
        <div class="muted mt-8">まだコメントはありません。</div>
      @endforelse

      <form method="post" action="{{ route('items.comments.store', $item) }}" class="mt-16">
        @csrf
        <label class="mb-8 d-block">商品へのコメント</label>
        <textarea name="body" rows="5" maxlength="255" class="w-100" placeholder="こちらにコメントを入力してください" required></textarea>
        @error('body')
          <div class="muted mt-8">{{ $message }}</div>
        @enderror
        <button type="submit" class="gt-btn gt-btn--comment mt-16">コメントを送信する</button>
      </form>
    </section>
  </div>
</div>
@endsection