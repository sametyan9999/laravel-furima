@extends('layouts.app')
@section('title','ログイン')

@section('header')
  @include('components.header_auth')
@endsection

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
  <div class="login-page">
    <div class="auth-container">
      <h1 class="page-title">ログイン</h1>

      <form class="auth-form" method="post" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input id="email"
                 type="email"
                 name="email"
                 value="{{ old('email') }}"
                 required
                 autocomplete="email"
                 autofocus>
          @error('email')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input id="password"
                 type="password"
                 name="password"
                 required
                 autocomplete="current-password">
          @error('password')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary btn-wide">ログインする</button>
        </div>

        <p class="help-link">
          <a href="{{ route('register') }}">会員登録はこちら</a>
        </p>
      </form>
    </div>
  </div>
@endsection