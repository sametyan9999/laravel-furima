@extends('layouts.app')
@section('title', '商品一覧')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/items.css') }}?v={{ filemtime(public_path('css/items.css')) }}">
@endpush

@section('content')
  <div class="tab">
    @php
      $tab = request('tab','recommend');
      // 検索語を保持（空文字でも消えないように）
      $q = request()->has('q') ? request('q') : null;
      $keep = is_null($q) ? [] : ['q'=>$q];
    @endphp

    <a class="tab__link {{ $tab==='recommend' ? 'is-active' : '' }}"
       href="{{ route('items.index', $keep) }}">おすすめ</a>

    <a class="tab__link {{ $tab==='mylist' ? 'is-active' : '' }}"
       href="{{ route('items.index', array_merge($keep, ['tab'=>'mylist'])) }}">マイリスト</a>
  </div>

  <div class="grid">
    @forelse($items as $item)
      @if($item->user_id !== auth()->id())
        <a href="{{ route('items.show', $item) }}" class="card">
          <div class="card__thumb">
            {{-- 画像はアクセサを利用。noimageは asset() に統一 + 遅延読み込み --}}
            <img src="{{ $item->image_url ?? asset('images/noimage.png') }}"
                 alt="{{ $item->name }}"
                 loading="lazy">
            {{-- 売済み判定：status か purchases 存在（is_sold） --}}
            @if(($item->status ?? null) === 'sold' || ($item->is_sold ?? false))
              <span class="card__badge">Sold</span>
            @endif
          </div>
          <div class="card__name">{{ $item->name }}</div>
        </a>
      @endif
    @empty
      <p class="mt-24">商品がありません。</p>
    @endforelse
  </div>

  {{-- ページネーション（検索条件・タブを保持） --}}
  @if($items instanceof \Illuminate\Contracts\Pagination\Paginator)
    <div class="mt-24">
      {{ $items->appends(request()->query())->links() }}
    </div>
  @endif
@endsection