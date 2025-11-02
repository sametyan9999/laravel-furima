{{-- resources/views/mypage/index.blade.php --}}
@extends('layouts.app')
@section('title','マイページ')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endpush

@section('content')
<div class="container mypage-hero">
  <div class="avatar-circle lg">
    @if(optional($profile)->avatar_path)
      <img src="{{ asset($profile->avatar_path) }}" alt="avatar">
    @endif
  </div>

  <div class="mypage-hero__info">
    <div class="mypage-hero__top">
      <div class="mypage-hero__name">{{ $user->name }}</div>
      <a href="{{ route('mypage.profile.edit') }}" class="btn btn-outline btn-sm mypage-hero__edit">プロフィールを編集</a>
    </div>
  </div>
</div>

<div class="container mt-24">
  {{-- ✅ タブは view パラメータで切替 --}}
  @php $view = $view ?? 'sell'; @endphp
  <div class="tab">
    <a class="tab__link {{ $view === 'sell' ? 'is-active' : '' }}"
       href="{{ route('mypage.index', ['view'=>'sell']) }}">出品した商品</a>
    <a class="tab__link {{ $view === 'buy' ? 'is-active' : '' }}"
       href="{{ route('mypage.index', ['view'=>'buy']) }}">購入した商品</a>
  </div>

  {{-- 一覧：カードグリッド表示 --}}
  @if($view === 'buy')
    @if($bought && $bought->count())
      <div class="grid grid--mypage">
        @foreach($bought as $p)
          @php $it = $p->item; @endphp
          @if($it)
            <a href="{{ route('items.show', $it) }}" class="card">
              <div class="card__thumb">
                <img src="{{ $it->image_url ?? $it->image }}" alt="{{ $it->name }}">
                {{-- 購入済みは常に Sold 表示 --}}
                <span class="badge-sold">Sold</span>
              </div>
              <div class="card__name">{{ $it->name }}</div>
            </a>
          @endif
        @endforeach
      </div>

      <div class="mt-16">
        {{ $bought->links() }}
      </div>
    @else
      <p class="mt-24 muted">購入した商品はありません。</p>
    @endif
  @else
    @if($sold && $sold->count())
      <div class="grid grid--mypage">
        @foreach($sold as $it)
          <a href="{{ route('items.show', $it) }}" class="card">
            <div class="card__thumb">
              <img src="{{ $it->image_url ?? $it->image }}" alt="{{ $it->name }}">
              @if(($it->status ?? null) === 'sold')
                <span class="badge-sold">Sold</span>
              @endif
            </div>
            <div class="card__name">{{ $it->name }}</div>
          </a>
        @endforeach
      </div>

      <div class="mt-16">
        {{ $sold->links() }}
      </div>
    @else
      <p class="mt-24 muted">出品した商品はありません。</p>
    @endif
  @endif
</div>
@endsection