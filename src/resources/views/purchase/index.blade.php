@extends('layouts.app')
@section('title','商品購入')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endpush

@section('content')
<div class="purchase">
  <div class="purchase__left">
    <div class="purchase__item">
      <img src="{{ $item->image }}" alt="" class="purchase__thumb">
      <div>
        <div class="purchase__name">{{ $item->name }}</div>
        <div class="purchase__price">¥ {{ number_format($item->price) }}</div>
      </div>
    </div>

    <section class="mt-24">
      <h2 class="section-title">支払い方法</h2>
      <select name="payment_method" form="purchaseForm">
        <option value="">選択してください</option>
        <option value="convenience">コンビニ払い</option>
        <option value="card">カード支払い</option>
      </select>
    </section>

    <section class="mt-24">
      <h2 class="section-title">配送先</h2>
      <div class="address-box">
        〒 {{ $snapshot['postal'] ?? 'XXX-YYYY' }}<br>
        ここには住所と建物が入ります
        <a class="ml-16" href="{{ route('purchase.address',$item) }}">変更する</a>
      </div>
    </section>
  </div>

  <aside class="purchase__summary">
    <table class="summary">
      <tr><th>商品代金</th><td>¥ {{ number_format($item->price) }}</td></tr>
      <tr><th>支払い方法</th><td id="payLabel">コンビニ払い</td></tr>
    </table>

    <form id="purchaseForm" class="mt-16" method="post" action="{{ route('purchase.store',$item) }}">
      @csrf
      <input type="hidden" name="payment_method" value="convenience">
      <button class="gt-btn gt-btn--buy w-100">購入する</button>
    </form>
  </aside>
</div>
@endsection