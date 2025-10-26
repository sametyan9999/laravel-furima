{{-- resources/views/purchase/address.blade.php --}}
@extends('layouts.app')
@section('title', '住所の変更')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/purchase-address.css') }}">
@endpush

@section('content')
<div class="address-page">
  <h1 class="page-title">住所の変更</h1>

  <form method="POST" action="{{ route('purchase.address.update', $item) }}" class="address-form">
    @csrf
    {{-- 郵便番号 --}}
    <div class="form-group">
      <label for="postal_code">郵便番号</label>
      <input
        id="postal_code"
        name="postal_code"
        type="text"
        inputmode="numeric"
        placeholder="123-4567"
        value="{{ old('postal_code', optional($profile)->postal_code) }}"
        required
      >
      @error('postal_code') <p class="error">{{ $message }}</p> @enderror
    </div>

    {{-- 住所 --}}
    <div class="form-group">
      <label for="address_line1">住所</label>
      <input
        id="address_line1"
        name="address_line1"
        type="text"
        value="{{ old('address_line1', optional($profile)->address_line1) }}"
        required
      >
      @error('address_line1') <p class="error">{{ $message }}</p> @enderror
    </div>

    {{-- 建物名（任意） --}}
    <div class="form-group">
      <label for="address_line2">建物名</label>
      <input
        id="address_line2"
        name="address_line2"
        type="text"
        value="{{ old('address_line2', optional($profile)->address_line2) }}"
      >
      @error('address_line2') <p class="error">{{ $message }}</p> @enderror
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary btn-wide">更新する</button>
    </div>
  </form>
</div>
@endsection