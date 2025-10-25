@extends('layouts.app')

@section('title','プロフィール設定')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endpush

@section('content')
<div class="mypage-profile-first">
  <h1 class="page-title">プロフィール設定</h1>

  {{-- POST送信に統一 --}}
  <form method="POST"
        action="{{ route('mypage.profile.update') }}"
        class="profile-form"
        enctype="multipart/form-data">
    @csrf

    {{-- アバター部分 --}}
    <div class="avatar-block">
      <div class="avatar-circle">
        @if(optional(auth()->user()->profile)->avatar_path)
          <img src="{{ asset(auth()->user()->profile->avatar_path) }}" alt="avatar">
        @endif
      </div>

      <label class="btn btn-outline btn-sm">
        画像を選択する
        <input type="file" name="avatar" accept="image/png,image/jpeg" hidden>
      </label>
      @error('avatar')<p class="error">{{ $message }}</p>@enderror
    </div>

    {{-- ユーザー名 --}}
    <div class="form-group">
      <label for="username">ユーザー名</label>
      <input id="username" name="username" type="text"
             value="{{ old('username', auth()->user()->name) }}" required>
      @error('username')<p class="error">{{ $message }}</p>@enderror
    </div>

    {{-- 郵便番号 --}}
    <div class="form-group">
      <label for="postal_code">郵便番号</label>
      <input id="postal_code" name="postal_code" type="text" inputmode="numeric" placeholder="123-4567"
             value="{{ old('postal_code', optional(auth()->user()->profile)->postal_code) }}">
      @error('postal_code')<p class="error">{{ $message }}</p>@enderror
    </div>

    {{-- 住所 --}}
    <div class="form-group">
      <label for="address_line1">住所</label>
      <input id="address_line1" name="address_line1" type="text"
             value="{{ old('address_line1', optional(auth()->user()->profile)->address_line1) }}">
      @error('address_line1')<p class="error">{{ $message }}</p>@enderror
    </div>

    {{-- 建物名 --}}
    <div class="form-group">
      <label for="address_line2">建物名</label>
      <input id="address_line2" name="address_line2" type="text"
             value="{{ old('address_line2', optional(auth()->user()->profile)->address_line2) }}">
      @error('address_line2')<p class="error">{{ $message }}</p>@enderror
    </div>

    {{-- 更新ボタン --}}
    <div class="form-actions">
      <button class="btn btn-primary btn-wide" type="submit">更新する</button>
    </div>
  </form>
</div>
@endsection