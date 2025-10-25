@extends('layouts.app')
@section('title','マイページ')

@section('content')
  <div class="mypage-head">
    <div class="avatar lg"></div>
    <div class="mypage-head__name">{{ auth()->user()->name }}</div>
    <a class="gt-btn gt-btn--white" href="{{ route('mypage.profile') }}">プロフィールを編集</a>
  </div>

  <div class="tab mt-16">
    @php $page=request('page','sell'); @endphp
    <a class="tab__link {{ $page==='sell'?'is-active':'' }}"
       href="{{ route('mypage.index',['page'=>'sell']) }}">出品した商品</a>
    <a class="tab__link {{ $page==='buy'?'is-active':'' }}"
       href="{{ route('mypage.index',['page'=>'buy']) }}">購入した商品</a>
  </div>

  <div class="grid mt-16">
    @foreach($items as $item)
      <a href="{{ route('items.show',$item) }}" class="card">
        <div class="card__thumb"><img src="{{ $item->image }}" alt=""></div>
        <div class="card__name">{{ $item->name }}</div>
      </a>
    @endforeach
  </div>
@endsection