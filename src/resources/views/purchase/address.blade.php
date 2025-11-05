@extends('layouts.app')
@section('title','住所の変更')

@section('content')
    <div class="form-box">
        <h1 class="form-title">住所の変更</h1>

        <form method="post" action="{{ route('purchase.address.update', $item) }}" novalidate>
            @csrf

            {{-- 郵便番号 --}}
            <label for="postal_code">郵便番号</label>
            <input
                id="postal_code"
                name="postal_code"
                type="text"
                inputmode="numeric"
                autocomplete="postal-code"
                value="{{ old('postal_code', $profile->postal_code) }}"
            >
            @error('postal_code')
                <div class="error">{{ $message }}</div>
            @enderror

            {{-- 住所 --}}
            <label for="address_line1" class="mt-12">住所</label>
            <input
                id="address_line1"
                name="address_line1"
                type="text"
                autocomplete="address-line1"
                value="{{ old('address_line1', $profile->address_line1) }}"
            >
            @error('address_line1')
                <div class="error">{{ $message }}</div>
            @enderror

            {{-- 建物名 --}}
            <label for="address_line2" class="mt-12">建物名</label>
            <input
                id="address_line2"
                name="address_line2"
                type="text"
                autocomplete="address-line2"
                value="{{ old('address_line2', $profile->address_line2) }}"
            >
            @error('address_line2')
                <div class="error">{{ $message }}</div>
            @enderror

            <button class="gt-btn gt-btn--primary mt-24">更新する</button>
        </form>
    </div>
@endsection