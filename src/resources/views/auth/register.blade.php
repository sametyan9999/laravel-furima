@extends('layouts.auth') {{-- ← 検索/出品の無いヘッダー版 --}}
@section('title','会員登録')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@section('content')
  <div class="register-page">
    <div class="auth-container">
      <h1 class="page-title">会員登録</h1>

      {{-- ★ ブラウザの必須チェックを無効化してサーバーバリデーションを確認 --}}
      <form class="auth-form" method="post" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="form-group">
          <label for="name">ユーザー名</label>
          <input id="name" type="text" name="name" value="{{ old('name') }}">
          @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input id="email" type="email" name="email" value="{{ old('email') }}">
          @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input id="password" type="password" name="password" autocomplete="new-password">
          @error('password') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="password_confirmation">確認用パスワード</label>
          <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary btn-wide">登録する</button>
        </div>

        <p class="help-link">
          <a class="no-underline" href="{{ route('login') }}">ログインはこちら</a>
        </p>
      </form>
    </div>
  </div>
@endsection