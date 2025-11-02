@extends('layouts.app')
@section('title', '商品一覧')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/items.css') }}?v={{ filemtime(public_path('css/items.css')) }}">
@endpush

@section('content')
  <div class="tab">
    @php
      $tab = request('tab','recommend');
      $keep = array_filter(['q'=>request('q')]);  // ← 検索語をタブ遷移でも維持
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
            @if($item->image_url)
              <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
            @endif
            @if(($item->status ?? null) === 'sold')
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
@endsection