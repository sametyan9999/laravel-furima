{{-- resources/views/items/mylist.blade.php --}}
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

  {{-- ✅ テストが見る見出し文言 --}}
  <h2 class="mt-16">いいね済み商品</h2>

  <div class="grid">
    @forelse($items as $item)
      <a href="{{ route('items.show',$item) }}" class="card">
        <div class="card__thumb">
          @if($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
          @endif

          {{-- ✅ 表記はテストに合わせて "Sold"（大文字小文字一致） --}}
          @if(($item->is_sold ?? false) || (($item->status ?? null) === 'sold'))
            <span class="card__badge">Sold</span>
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