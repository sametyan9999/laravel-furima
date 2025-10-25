@extends('layouts.app')
@section('title','ログイン')

@section('content')
  <div class="auth-box">
    <h1 class="auth-title">ログイン</h1>
    <form method="post" action="{{ route('login') }}">
      @csrf
      <label>メールアドレス</label>
      <input type="email" name="email" required>

      <label class="mt-16">パスワード</label>
      <input type="password" name="password" required>

      <button class="gt-btn gt-btn--primary mt-24 w-100">ログインする</button>

      <p class="mt-16 t-center"><a href="{{ route('register') }}">会員登録はこちら</a></p>
    </form>
  </div>
@endsection