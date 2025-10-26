@extends('layouts.app')
@section('title','商品購入')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endpush

@section('content')
<div class="purchase">
  <div class="purchase__left">
    <div class="purchase__item">
      @if($item->image_url)
        <img src="{{ $item->image_url }}" alt="" class="purchase__thumb">
      @endif
      <div>
        <div class="purchase__name">{{ $item->name }}</div>
        <div class="purchase__price">¥ {{ number_format($item->price) }}</div>
      </div>
    </div>

    <section class="purchase__section">
      <div class="section-head">
        <h2 class="section-title">支払い方法</h2>
      </div>
      <select id="paymentSelect" name="payment_method" form="purchaseForm">
        <option value="" disabled selected hidden>選択してください</option>
        <option value="convenience">コンビニ払い</option>
        <option value="card">カード支払い</option>
      </select>
    </section>

    <section class="purchase__section">
      <div class="section-head">
        <h2 class="section-title">配送先</h2>
        <a class="section-action" href="{{ route('purchase.address',$item) }}">変更する</a>
      </div>

      <div class="address-box">
        <div class="address-line">〒 {{ optional($profile)->postal_code ?? 'XXX-YYYY' }}</div>
        <div class="address-line">
          @php
            $addr1 = optional($profile)->address_line1;
            $addr2 = optional($profile)->address_line2;
          @endphp
          {{ $addr1 ? $addr1 : 'ここには住所と建物が入ります' }}
          @if($addr2)
            <br>{{ $addr2 }}
          @endif
        </div>
      </div>
    </section>
  </div>

  <aside class="purchase__summary">
    <table class="summary">
      <tr>
        <th>商品代金</th>
        <td>¥ {{ number_format($item->price) }}</td>
      </tr>
      <tr>
        <th>支払い方法</th>
        <td id="payLabel">コンビニ払い</td>
      </tr>
    </table>

    <form id="purchaseForm" class="mt-16" method="post" action="{{ route('purchase.store',$item) }}">
      @csrf
      <input id="paymentHidden" type="hidden" name="payment_method" value="convenience">
      <button class="gt-btn gt-btn--buy w-100">購入する</button>
    </form>
  </aside>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const select = document.getElementById('paymentSelect');
  const label  = document.getElementById('payLabel');
  const hidden = document.getElementById('paymentHidden');
  const map    = { convenience: 'コンビニ払い', card: 'カード支払い' };

  label.textContent = 'コンビニ払い';
  hidden.value = 'convenience';

  select.addEventListener('change', () => {
    const val = select.value;
    if (map[val]) {
      label.textContent = map[val];
      hidden.value = val;
    }
  });
});
</script>
@endpush