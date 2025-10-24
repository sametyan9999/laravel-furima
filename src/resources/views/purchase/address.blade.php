@extends('layouts.app')

@section('title','住所の変更')

@section('content')
  <h1 class="section-title" style="text-align:center">住所の変更</h1>
  <div class="container" style="max-width:680px;margin:20px auto 60px;">
    <form method="POST" action="{{ url('/purchase/address/'.$item->id) }}">
      @csrf
      <div class="mt16">
        <label>郵便番号</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $profile->postal_code ?? '') }}">
      </div>
      <div class="mt16">
        <label>住所</label>
        <input type="text" name="address1" value="{{ old('address1', $profile->address_line1 ?? '') }}">
      </div>
      <div class="mt16">
        <label>建物名</label>
        <input type="text" name="address2" value="{{ old('address2', $profile->address_line2 ?? '') }}">
      </div>
      <div class="mt24">
        <button class="btn btn-block">更新する</button>
      </div>
    </form>
  </div>
@endsection