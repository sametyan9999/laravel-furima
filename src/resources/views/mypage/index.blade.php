@extends('layouts.app')

@section('title','マイページ')

@section('content')
  <div class="mt24">
    <div class="flex" style="align-items:center">
      <div style="width:84px;height:84px;border-radius:50%;background:#ddd"></div>
      <h2 style="margin:0 0 0 16px;font-weight:800">{{ auth()->user()->name ?? 'ユーザー名' }}</h2>
      <div style="margin-left:auto">
        <a class="btn" href="{{ route('mypage.profile.edit') }}">プロフィールを編集</a>
      </div>
    </div>

    <div class="tabs mt24">
      @php $tab = request('page','sell'); @endphp
      <a href="{{ route('mypage.index',['page'=>'sell']) }}" class="{{ $tab==='sell'?'active':'' }}">出品した商品</a>
      <a href="{{ route('mypage.index',['page'=>'buy']) }}" class="{{ $tab==='buy'?'active':'' }}">購入した商品</a>
    </div>

    <div class="grid mt24">
      @foreach($items as $item)
        @include('components.item-card',['item'=>$item])
      @endforeach
    </div>
  </div>
@endsection