@extends('layouts.app')

@section('title','購入確認')

@section('content')
  <div class="flex mt24">
    <div style="flex:1">
      {{-- 商品要約 --}}
      <div class="flex" style="align-items:center;gap:16px;">
        <div class="thumb" style="width:72px;height:72px">商品画像</div>
        <div>
          <div style="font-weight:700">{{ $item->name }}</div>
          <div class="price">¥{{ number_format($item->price) }}</div>
        </div>
      </div>

      <hr class="mt24 mb24">

      {{-- 支払い方法 --}}
      <div class="mt24">
        <div class="section-title">支払い方法</div>
        <form id="purchase-form" method="POST" action="{{ route('purchase.store', $item) }}">
          @csrf
          <select name="payment_method" required>
            <option value="">選択してください</option>
            <option value="convenience" {{ old('payment_method')==='convenience'?'selected':'' }}>コンビニ払い</option>
            <option value="card" {{ old('payment_method')==='card'?'selected':'' }}>カード支払い</option>
          </select>

          {{-- 配送先（スナップショット） --}}
          <div class="mt32">
            <div class="section-title">配送先</div>
            <div class="muted">〒 {{ $address['postal_code'] ?? 'XXX-YYYY' }}</div>
            <div class="muted">{{ $address['address'] ?? 'ここには住所と建物が入ります' }}</div>
            <div class="mt8"><a href="{{ url('/purchase/address/'.$item->id) }}">変更する</a></div>
          </div>

          <div class="mt24">
            <button class="btn">購入する</button>
          </div>
        </form>
      </div>
    </div>

    {{-- 右：合計 --}}
    <div style="flex:0 0 340px;margin-left:auto">
      <div style="border:1px solid #ccc;border-radius:6px;overflow:hidden">
        <div style="display:flex;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #eee">
          <div>商品代金</div><div>¥{{ number_format($item->price) }}</div>
        </div>
        <div style="display:flex;justify-content:space-between;padding:14px 16px;">
          <div>支払い方法</div><div class="muted">{{ old('payment_method','選択前')==='card'?'カード払い':(old('payment_method')==='convenience'?'コンビニ払い':'—') }}</div>
        </div>
      </div>
    </div>
  </div>
@endsection