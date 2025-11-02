@extends('layouts.app')
@section('title', '商品一覧')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/items.css') }}?v={{ filemtime(public_path('css/items.css')) }}">
@endpush

@section('content')
  <div class="tab">
    @php $tab = request('tab','recommend'); @endphp
    <a class="tab__link {{ $tab==='recommend' ? 'is-active' : '' }}"
       href="{{ route('items.index') }}">おすすめ</a>
    <a class="tab__link {{ $tab==='mylist' ? 'is-active' : '' }}"
       href="{{ route('items.index', ['tab'=>'mylist']) }}">マイリスト</a>
  </div>

  <div class="grid">
    @forelse($items as $item)
      {{-- ✅ 自分の出品は一覧に表示しない --}}
      @if($item->user_id !== auth()->id())
        <a href="{{ route('items.show', $item) }}" class="card">
          <div class="card__thumb">
            @if($item->image_url)
              <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
            @endif

            {{-- ✅ 購入済み（sold）ならバッジ表示 --}}
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