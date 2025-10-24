@extends('layouts.app')

@section('title',$item->name)

@section('content')
  <div class="flex mt24" style="align-items:flex-start;">
    {{-- 左：大画像 --}}
    <div style="flex:0 0 420px">
      <div class="thumb" style="width:100%;aspect-ratio:1/1;">
        @if($item->images->first())
          <img src="{{ asset('storage/'.$item->images->first()->path) }}" alt="{{ $item->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">
        @else
          商品画像
        @endif
      </div>
    </div>

    {{-- 右：情報 --}}
    <div style="flex:1">
      <h1 style="font-size:28px;font-weight:800;margin:0 0 6px">{{ $item->name }}</h1>
      <div class="muted mb24">{{ $item->brand ?: 'ブランド名' }}</div>
      <div style="font-size:26px;font-weight:800;margin-bottom:8px;">¥{{ number_format($item->price) }} <span class="muted" style="font-size:14px;">(税込)</span></div>

      {{-- いいね/コメントアイコン（ダミー・実装時に差替） --}}
      <div class="muted mb24">⭐ 3　💬 1</div>

      {{-- 購入ボタン --}}
      <a class="btn btn-block" href="{{ route('purchase.index', $item) }}">購入手続きへ</a>

      <div class="section-title">商品説明</div>
      <div class="muted">
        {!! nl2br(e($item->description ?? "新品\n商品の状態は良好です。傷もありません。\n購入後、即発送いたします。")) !!}
      </div>

      <div class="section-title">商品の情報</div>
      <div class="mb24">
        <div>カテゴリ：<span class="badge">{{ $item->category->name ?? '—' }}</span></div>
        <div class="mt8">商品の状態：<span class="badge">{{ $item->condition->name ?? '—' }}</span></div>
      </div>

      {{-- コメント --}}
      <div class="section-title">コメント ({{ $item->comments->count() }})</div>
      <div class="mb24">
        @forelse($item->comments as $c)
          <div class="flex" style="align-items:center;margin-bottom:8px;">
            <div style="width:36px;height:36px;border-radius:50%;background:#ddd"></div>
            <div><strong class="muted" style="margin-left:8px;">{{ $c->user->name ?? 'user' }}</strong></div>
          </div>
          <div class="muted" style="background:#eee;padding:10px;border-radius:6px;margin-bottom:12px;">{{ $c->body }}</div>
        @empty
          <div class="muted">コメントはまだありません。</div>
        @endforelse
      </div>

      @auth
      <div class="section-title">商品へのコメント</div>
      <form class="mt8" action="{{ route('comments.store', $item) }}" method="POST">
        @csrf
        <textarea name="body" placeholder="こちらにコメントが入ります。"></textarea>
        <div class="mt16"><button class="btn">コメントを送信する</button></div>
      </form>
      @endauth
    </div>
  </div>
@endsection