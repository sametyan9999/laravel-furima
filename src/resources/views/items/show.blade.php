@extends('layouts.app')
@section('title', $item->name)

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/items.css') }}?v={{ filemtime(public_path('css/items.css')) }}">
@endpush

@section('content')
<div class="detail">
  <div class="detail__left">
    <div class="detail__image-wrap">
      @if($item->image)
        <img class="detail__image" src="{{ $item->image }}" alt="{{ $item->name }}">
      @endif
      {{-- ✅ 詳細でも sold を表示 --}}
      @if(($item->status ?? null) === 'sold')
        <span class="card__badge" aria-label="売り切れ">Sold</span>
      @endif
    </div>
  </div>

  <div class="detail__right">
    <h1 class="detail__title">{{ $item->name }}</h1>
    <div class="detail__brand">{{ $item->brand ?? 'ブランド名' }}</div>

    <div class="detail__price">
      ¥{{ number_format($item->price) }} <small>（税込）</small>
    </div>

    <div class="detail__icons" aria-label="ステータス">
      @auth
        {{-- ✅ いいねトグル：未いいね→POST /like、いいね済み→DELETE /unlike --}}
        @if($liked)
          <form method="POST" action="{{ route('items.unlike', $item) }}" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="icon-like is-liked"
                    aria-pressed="true"
                    title="いいね解除">★</button>
          </form>
        @else
          <form method="POST" action="{{ route('items.like', $item) }}" style="display:inline;">
            @csrf
            <button type="submit"
                    class="icon-like"
                    aria-pressed="false"
                    title="いいねする">★</button>
          </form>
        @endif
      @else
        <a href="{{ route('login') }}" class="icon-like" title="ログインしていいね">★</a>
      @endauth
      <small class="ml-8">{{ $item->likes_count }}</small>

      <span class="ml-16" aria-hidden="true">💬</span>
      <small class="ml-8">{{ $item->comments_count }}</small>
    </div>

    {{-- ✅ sold のときは購入不可 --}}
    @if(($item->status ?? null) === 'sold')
      <button class="gt-btn gt-btn--buy mt-16" disabled>売り切れ</button>
    @else
      <a href="{{ route('purchase.index', $item) }}" class="gt-btn gt-btn--buy mt-16">購入手続きへ</a>
    @endif

    <section class="detail__section">
      <h2>商品説明</h2>
      <p class="detail__desc">{{ $item->description ?? '' }}</p>
    </section>

    <section class="detail__section">
      <h2>商品の情報</h2>
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
      <div class="detail-kv">
        <div class="detail-kv__label">商品の状態</div>
        <div class="detail-kv__value">{{ $item->condition->name ?? '状態未設定' }}</div>
      </div>
    </section>

    <section class="detail__section">
      <h2>コメント（{{ $comments->count() }}）</h2>

      {{-- ✅ コメントがある場合のみ表示 --}}
      @foreach($comments as $c)
        <div class="comment">
          <div class="avatar"></div>
          <div class="comment__meta">
            <div class="comment__name">{{ $c->user->name ?? 'ゲスト' }}</div>
          </div>
        </div>
        <div class="comment__bubble">{{ $c->body }}</div>
      @endforeach

      {{-- ✅ コメント投稿フォーム --}}
      <form method="POST" action="{{ route('items.comments.store', $item) }}" class="mt-16" novalidate>
        @csrf
        <label for="comment-body" class="mb-8 d-block">商品へのコメント</label>
        <textarea id="comment-body"
                  name="body"
                  rows="5"
                  class="w-100"
                  placeholder="こちらにコメントを入力してください">{{ old('body') }}</textarea>

        @error('body')
          <div class="muted mt-8">{{ $message }}</div>
        @enderror

        <button type="submit" class="gt-btn gt-btn--comment mt-16">コメントを送信する</button>
      </form>
    </section>
  </div>
</div>
@endsection