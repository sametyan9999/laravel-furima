{{-- resources/views/mypage/profile.blade.php --}}
@extends('layouts.app')
@section('title','プロフィール設定')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endpush

@section('content')
  <div class="profile-edit">
    <h1 class="page-title">プロフィール設定</h1>

    <form method="POST"
          action="{{ route('mypage.profile.update') }}"
          enctype="multipart/form-data"
          class="profile-form">
      @csrf

      {{-- アバター & 画像選択 --}}
      <div class="avatar-block">
        <div class="avatar-circle">
          @php
            $avatar = optional(auth()->user()->profile)->avatar_path;
          @endphp
          @if($avatar)
            <img id="avatarPreview" src="{{ asset($avatar) }}" alt="avatar">
          @else
            <img id="avatarPreview" src="" alt="" style="display:none;">
          @endif
        </div>

        {{-- input は非表示にして JS でクリックを発火 --}}
        <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png" style="display:none;">
        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('avatar').click();">
          画像を選択する
        </button>
      </div>
      @error('avatar') <div class="error">{{ $message }}</div> @enderror

      {{-- ユーザー名 --}}
      <div class="form-group">
        <label for="username">ユーザー名</label>
        <input id="username" name="username" type="text"
               value="{{ old('username', auth()->user()->name) }}" required>
        @error('username') <div class="error">{{ $message }}</div> @enderror
      </div>

      {{-- 郵便番号 --}}
      <div class="form-group">
        <label for="postal_code">郵便番号</label>
        <input id="postal_code" name="postal_code" type="text" inputmode="numeric" placeholder="123-4567"
               value="{{ old('postal_code', optional($profile)->postal_code) }}" required>
        @error('postal_code') <div class="error">{{ $message }}</div> @enderror
      </div>

      {{-- 住所 --}}
      <div class="form-group">
        <label for="address_line1">住所</label>
        <input id="address_line1" name="address_line1" type="text"
               value="{{ old('address_line1', optional($profile)->address_line1) }}" required>
        @error('address_line1') <div class="error">{{ $message }}</div> @enderror
      </div>

      {{-- 建物名 --}}
      <div class="form-group">
        <label for="address_line2">建物名</label>
        <input id="address_line2" name="address_line2" type="text"
               value="{{ old('address_line2', optional($profile)->address_line2) }}">
        @error('address_line2') <div class="error">{{ $message }}</div> @enderror
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-wide">更新する</button>
      </div>
    </form>
  </div>

  {{-- 画像プレビュー（選択直後に丸の中へ表示） --}}
  <script>
    (function () {
      const input = document.getElementById('avatar');
      const preview = document.getElementById('avatarPreview');
      if (!input) return;

      input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      });
    })();
  </script>
@endsection