@extends('layouts.app')
@section('title','住所の変更')

@section('content')
  <div class="form-box">
    <h1 class="form-title">住所の変更</h1>
    <form method="post" action="{{ route('purchase.address.update',$item) }}">
      @csrf
      <label>郵便番号</label>
      <input name="postal_code" value="{{ old('postal_code',$profile->postal_code) }}">

      <label class="mt-12">住所</label>
      <input name="address_line1" value="{{ old('address_line1',$profile->address_line1) }}">

      <label class="mt-12">建物名</label>
      <input name="address_line2" value="{{ old('address_line2',$profile->address_line2) }}">

      <button class="gt-btn gt-btn--primary mt-24">更新する</button>
    </form>
  </div>
@endsection