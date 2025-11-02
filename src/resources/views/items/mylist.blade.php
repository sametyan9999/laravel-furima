{{-- resources/views/items/mylist.blade.php --}}
@extends('layouts.app')
@section('title','マイリスト')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/items.css') }}">
@endpush

@section('content')
  <div class="tab">
    <a class="tab__link" href="{{ route('items.index') }}">おすすめ</a>
    {{-- どちらのルートでもOK。統一したいなら index?tab=mylist に寄せる --}}
    <a class="tab__link is-active" href="{{ route('items.mylist') }}">マイリスト</a>
  </div>

  <div class="grid">
    @forelse($items as $item)
      {{-- 自分の出品は非表示にするならこの行を有効化
      @if($item->user_id !== auth()->id()) --}}
        <a href="{{ route('items.show',$item) }}" class="card">
          <div class="card__thumb">
            @if($item->image_url)
              <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
            @endif

            {{-- ✅ SOLD 判定は accessor を優先。保険で status も見る --}}
            @if(($item->is_sold ?? false) || (($item->status ?? null) === 'sold'))
              <span class="card__badge">SOLD</span>
            @endif
          </div>
          <div class="card__name">{{ $item->name }}</div>
        </a>
      {{-- @endif --}}
    @empty
      <p class="mt-24">いいね済み商品がありません。</p>
    @endforelse
  </div>

  <div class="mt-24">
    {{ $items->links() }}
  </div>
@endsection