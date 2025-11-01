@extends('layouts.app')
@section('title','マイリスト')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/items.css') }}">
@endpush

@section('content')
  <div class="tab">
    <a class="tab__link" href="{{ route('items.index') }}">おすすめ</a>
    <a class="tab__link is-active" href="{{ route('items.mylist') }}">マイリスト</a>
  </div>

  <div class="grid">
    @forelse($items as $item)
      <a href="{{ route('items.show',$item) }}" class="card">
        <div class="card__thumb">
          @if($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
          @endif
          @if($item->purchase || ($item->status ?? null) !== 'on_sale')
            <span class="badge-sold">Sold</span>
          @endif
        </div>
        <div class="card__name">{{ $item->name }}</div>
      </a>
    @empty
      <p class="mt-24">いいね済み商品がありません。</p>
    @endforelse
  </div>

  <div class="mt-24">
    {{ $items->links() }}
  </div>
@endsection
