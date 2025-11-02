@extends('layouts.app')
@section('title','商品購入')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endpush

@section('content')
<div class="purchase">
  {{-- エラー全体表示 --}}
  @if ($errors->any())
    <div class="gt-alert gt-alert--danger mb-16">
      <ul class="m-0 pl-16">
        @foreach ($errors->all() as $message)
          <li>{{ $message }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="purchase__left">
    <div class="purchase__item">
      @if($item->image)
        <img src="{{ $item->image }}" alt="" class="purchase__thumb">
      @endif
      <div>
        <div class="purchase__name">{{ $item->name }}</div>
        <div class="purchase__price">¥ {{ number_format($item->price) }}</div>
      </div>
    </div>

    {{-- 支払い方法 --}}
    <section class="purchase__section">
      <div class="section-head">
        <h2 class="section-title">支払い方法</h2>
      </div>
      <select id="paymentSelect"
              name="payment_method"
              form="purchaseForm"
              required>
        <option value="convenience" {{ ($initialPayment ?? 'convenience')==='convenience' ? 'selected' : '' }}>コンビニ払い</option>
        <option value="card"        {{ ($initialPayment ?? 'convenience')==='card' ? 'selected' : '' }}>カード支払い</option>
      </select>
      @error('payment_method')
        <div class="text-danger small mt-4">{{ $message }}</div>
      @enderror
    </section>

    {{-- 配送先 --}}
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
      @error('address')
        <div class="text-danger small mt-4">{{ $message }}</div>
      @enderror
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
        <td id="payLabel">{{ $paymentLabel ?? 'コンビニ払い' }}</td>
      </tr>
    </table>

    <form id="purchaseForm" class="mt-16" method="POST" action="{{ route('purchase.store',$item) }}">
      @csrf
      <button type="submit" class="gt-btn gt-btn--buy w-100">購入する</button>
    </form>
    @error('purchase')
      <div class="text-danger small mt-4">{{ $message }}</div>
    @enderror
  </aside>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const select = document.getElementById('paymentSelect');
  const label  = document.getElementById('payLabel');
  const map    = { convenience: 'コンビニ払い', card: 'カード支払い' };
  label.textContent = map[select.value] || 'コンビニ払い';
  select.addEventListener('change', () => { label.textContent = map[select.value] || ''; });
});
</script>
@endpush