{{-- 商品カード（一覧・おすすめ・マイリスト兼用） --}}
@props(['item'])

@php
    // 最初の1枚をサムネに（sort→id）
    $img = $item->images->sortBy(['sort','id'])->first();
    $imgUrl = $img?->url ?? asset('images/noimage.png'); // プレースホルダ
@endphp

<a class="card" href="{{ route('items.show', $item) }}">
  <div class="card__thumb" style="aspect-ratio:1/1; overflow:hidden;">
    <img src="{{ $imgUrl }}" alt="{{ $item->name }}" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;">
  </div>
  <div class="card__body">
    <p class="card__title">{{ $item->name }}</p>
    <p class="card__price">¥{{ number_format($item->price) }}</p>
  </div>
</a>