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
  {{-- タブ切替（出品した商品 / 購入した商品） --}}
  @php $page = $page ?? null; @endphp
  <div class="tab">
    <a class="tab__link {{ $page !== 'buy' ? 'is-active' : '' }}"
       href="{{ route('mypage.index') }}">出品した商品</a>
    <a class="tab__link {{ $page === 'buy' ? 'is-active' : '' }}"
       href="{{ route('mypage.index', ['page'=>'buy']) }}">購入した商品</a>
  </div>

  {{-- 一覧：カードグリッド表示 --}}
  @if($page === 'buy')
    @if($bought && $bought->count())
      <div class="grid grid--mypage">
        @foreach($bought as $p)
          <a href="{{ route('items.show', $p->item) }}" class="card">
            <div class="card__thumb">
              <img src="{{ $p->item->image }}" alt="{{ $p->item->name }}">
            </div>
            <div class="card__name">{{ $p->item->name }}</div>
          </a>
        @endforeach
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
              <img src="{{ $it->image }}" alt="{{ $it->name }}">
            </div>
            <div class="card__name">{{ $it->name }}</div>
          </a>
        @endforeach
      </div>
    @else
      <p class="mt-24 muted">出品した商品はありません。</p>
    @endif
  @endif
</div>
@endsection