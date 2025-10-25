@extends('layouts.app')

@section('title','会員登録')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<div class="container auth-container">
  <h1 class="page-title">会員登録</h1>

  <form method="POST" action="{{ route('register') }}" class="form">
    @csrf
    <div class="form-group">
      <label for="name">ユーザー名</label>
      <input id="name" name="name" type="text" value="{{ old('name') }}" required>
      @error('name')<p class="error">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
      <label for="email">メールアドレス</label>
      <input id="email" name="email" type="email" value="{{ old('email') }}" required>
      @error('email')<p class="error">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
      <label for="password">パスワード</label>
      <input id="password" name="password" type="password" minlength="8" required>
      @error('password')<p class="error">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
      <label for="password_confirmation">確認用パスワード</label>
      <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary btn-wide">登録する</button>
    </div>

    <p class="help-link">
      <a href="{{ route('login') }}">ログインはこちら</a>
    </p>
  </form>
</div>
@endsection